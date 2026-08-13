"use client";

import { useState, useEffect } from "react";
import { useForm, useController } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useQueryClient } from "@tanstack/react-query";
import { gallerySchema, type GalleryFormValues } from "@/schemas/gallery";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import { Textarea } from "@/components/ui/Textarea";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { ApiSelect } from "@/components/ui/ApiSelect";
import { uploadMedia } from "@/lib/query-utils";
import { api } from "@/lib/api";
import { removeEmptyStrings } from "@/lib/form-utils";
import { Spinner } from "@/components/ui/Spinner";
import { getApiErrorMessage } from "@/lib/query-client";
import type { Activity, Gallery } from "@/types/models";
import type { MediaObject } from "@/lib/api-types";

interface GalleryItemMedia extends MediaObject {
  photoId?: string; // ID row dari tabel gallery_photos di database (jika sudah ada)
}

interface GalleryFormProps {
  initial?: Gallery | null;
  onSubmit?: (values: GalleryFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function GalleryForm({ initial, onCancel }: GalleryFormProps) {
  const queryClient = useQueryClient();
  const [isUploadingCover, setIsUploadingCover] = useState(false);
  const [isUploadingItems, setIsUploadingItems] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [uploadedMediaList, setUploadedMediaList] = useState<GalleryItemMedia[]>([]);
  const [removedPhotoIds, setRemovedPhotoIds] = useState<string[]>([]);
  const [uploadError, setUploadError] = useState<string | null>(null);

  // Initialize existing photos in edit mode
  useEffect(() => {
    if (initial?.photos && initial.photos.length > 0) {
      const existingMedia: GalleryItemMedia[] = initial.photos.map((p) => ({
        id: p.mediaId || p.id,
        photoId: p.id,
        url: p.url || "",
        name: p.caption || "File",
        mimeType: "image/jpeg",
      }));
      setUploadedMediaList(existingMedia);
    }
  }, [initial]);

  const {
    register,
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<GalleryFormValues>({
    resolver: zodResolver(gallerySchema),
    defaultValues: {
      galleryCategoryId: initial?.galleryCategoryId ?? "",
      activityId: initial?.activityId ?? "",
      title: initial?.title ?? "",
      description: initial?.description ?? "",
      coverPhotoMediaId: initial?.coverPhoto?.id ?? "",
    },
  });

  const { field: coverField } = useController({ name: "coverPhotoMediaId", control });

  // Multiple File Upload Handler (Photos & Videos)
  const handleMultipleFilesSelect = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploadError(null);
    setIsUploadingItems(true);

    const newMediaItems: GalleryItemMedia[] = [];

    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      try {
        const media = await uploadMedia(file, "gallery-photo");
        newMediaItems.push(media);

        // If cover is not set yet, automatically set the first uploaded image as cover!
        if (!coverField.value && media.mimeType?.startsWith("image/")) {
          coverField.onChange(media.id);
        }
      } catch (err: unknown) {
        const message = getApiErrorMessage(err);
        setUploadError(message);
      }
    }

    setUploadedMediaList((prev) => [...prev, ...newMediaItems]);
    setIsUploadingItems(false);
    e.target.value = "";
  };

  const handleRemoveMedia = (mediaId: string) => {
    const itemToRemove = uploadedMediaList.find((m) => m.id === mediaId);
    if (itemToRemove?.photoId) {
      setRemovedPhotoIds((prev) => [...prev, itemToRemove.photoId!]);
    }
    setUploadedMediaList((prev) => prev.filter((m) => m.id !== mediaId));
  };

  const handleFormSubmit = async (values: GalleryFormValues) => {
    try {
      setIsSubmitting(true);
      setUploadError(null);

      const payload = removeEmptyStrings(values);
      let galleryId = initial?.id;

      if (galleryId) {
        // Edit mode
        await api.put(`/admin/galleries/${galleryId}`, payload);
      } else {
        // Create mode
        const res = await api.post("/admin/galleries", payload);
        galleryId = res.data.data.id;
      }

      // 1. Delete removed photos from database if editing
      if (galleryId && removedPhotoIds.length > 0) {
        for (const photoId of removedPhotoIds) {
          try {
            await api.delete(`/admin/galleries/${galleryId}/photos/${photoId}`);
          } catch (err) {
            console.error(`Failed to remove photo ${photoId}`, err);
          }
        }
      }

      // 2. Attach newly added photos/videos (which don't have photoId yet)
      const newMediaIds = uploadedMediaList.filter((m) => !m.photoId).map((m) => m.id);
      if (galleryId && newMediaIds.length > 0) {
        await api.post(`/admin/galleries/${galleryId}/photos`, { mediaIds: newMediaIds });
      }

      // Invalidate queries so admin & public reflect the new data immediately
      await queryClient.invalidateQueries({ queryKey: ["/admin/galleries"] });
      await queryClient.invalidateQueries({ queryKey: ["public", "galleries"] });

      onCancel(); // Close modal
    } catch (err: unknown) {
      setUploadError(getApiErrorMessage(err));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-5">
      <CategorySelect
        label="Kategori Galeri *"
        endpoint="/admin/gallery-categories"
        {...register("galleryCategoryId")}
        error={errors.galleryCategoryId?.message}
      />

      <div>
        <Label htmlFor="title">Judul Album *</Label>
        <Input id="title" {...register("title")} error={errors.title?.message} />
      </div>

      <ApiSelect<Activity>
        id="activityId"
        endpoint="/admin/activities"
        label="Kegiatan Terkait"
        placeholder="Tidak terhubung kegiatan"
        getOptionLabel={(activity) => activity.title}
        {...register("activityId")}
        error={errors.activityId?.message}
      />

      {/* Cover Photo */}
      <MediaReferenceField
        collection="gallery-photo"
        label="Foto Cover Utama Album"
        currentMedia={initial?.coverPhoto ?? null}
        onMediaChange={(id) => coverField.onChange(id ?? "")}
        onUploadingChange={setIsUploadingCover}
        error={errors.coverPhotoMediaId?.message}
        disabled={isSubmitting}
      />

      {/* Multiple Photos & Videos Upload */}
      <div className="space-y-3 pt-2 border-t border-outline/10">
        <div className="flex items-center justify-between">
          <Label className="text-body-md font-bold text-[#004b36]">
            Upload Dokumentasi (Foto &amp; Video) — Bisa Pilih Beberapa
          </Label>
          <span className="text-label-sm text-on-surface-variant">
            {uploadedMediaList.length} file terunggah
          </span>
        </div>

        <div className="rounded-2xl border-2 border-dashed border-[#004b36]/30 bg-[#004b36]/5 p-5 text-center transition-colors hover:bg-[#004b36]/10">
          <input
            type="file"
            id="multi-file-input"
            multiple
            accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"
            onChange={handleMultipleFilesSelect}
            className="hidden"
            disabled={isUploadingItems || isSubmitting}
          />
          <label htmlFor="multi-file-input" className="cursor-pointer flex flex-col items-center justify-center space-y-2">
            <span className="material-symbols-outlined text-[36px] text-[#004b36]">cloud_upload</span>
            <span className="text-body-md font-bold text-[#004b36]">
              Klik untuk memilih Foto &amp; Video sekaligus
            </span>
            <span className="text-label-sm text-on-surface-variant">
              Format: JPG, PNG, WebP, MP4, WebM, MOV (Maks. 100 MB per file)
            </span>
          </label>
        </div>

        {isUploadingItems && (
          <div className="flex items-center justify-center gap-2 py-3 text-body-sm text-[#004b36] font-semibold">
            <Spinner className="w-4 h-4" />
            Mengunggah beberapa file foto/video...
          </div>
        )}

        {uploadError && <p className="text-label-sm text-rose-600 font-semibold">{uploadError}</p>}

        {/* Uploaded Items Thumbnails Grid */}
        {uploadedMediaList.length > 0 && (
          <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 pt-2 max-h-60 overflow-y-auto p-1">
            {uploadedMediaList.map((media, idx) => {
              const isVideo = media.mimeType?.startsWith("video/") || media.url?.match(/\.(mp4|webm|mov)$/i);

              return (
                <div key={media.id || idx} className="relative group h-24 rounded-xl overflow-hidden border border-outline/10 bg-slate-900 shadow-2xs">
                  {isVideo ? (
                    <div className="w-full h-full flex flex-col items-center justify-center bg-slate-800 text-amber-300">
                      <span className="material-symbols-outlined text-[28px]">movie</span>
                      <span className="text-[10px] truncate max-w-[80%]">{media.name || "Video"}</span>
                    </div>
                  ) : (
                    <img src={media.url} alt={media.name || "Foto"} className="w-full h-full object-cover" />
                  )}

                  <button
                    type="button"
                    onClick={() => handleRemoveMedia(media.id)}
                    className="absolute top-1 right-1 h-6 w-6 rounded-full bg-rose-600 text-white flex items-center justify-center opacity-90 hover:opacity-100 transition-opacity"
                    title="Hapus Foto/Video"
                  >
                    <span className="material-symbols-outlined text-[14px]">close</span>
                  </button>
                </div>
              );
            })}
          </div>
        )}
      </div>

      <div>
        <Label htmlFor="description">Deskripsi Album</Label>
        <Textarea id="description" {...register("description")} error={errors.description?.message} />
      </div>

      <div className="flex justify-end gap-3 pt-4 border-t border-outline/10">
        <Button type="button" variant="outline" onClick={onCancel} disabled={isSubmitting}>
          Batal
        </Button>
        <Button type="submit" loading={isSubmitting} disabled={isUploadingCover || isUploadingItems || isSubmitting}>
          Simpan Album Galeri
        </Button>
      </div>
    </form>
  );
}
