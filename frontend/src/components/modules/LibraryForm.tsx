"use client";

import { useState } from "react";
import { useForm, useController } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { librarySchema, type LibraryFormValues } from "@/schemas/library";
import { Button } from "@/components/ui/Button";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import type { LibraryDocument } from "@/types/models";

interface LibraryFormProps {
  initial?: LibraryDocument | null;
  onSubmit: (values: LibraryFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function LibraryForm({ initial, onSubmit, onCancel, loading }: LibraryFormProps) {
  const [isUploading, setIsUploading] = useState(false);

  const { register, control, handleSubmit, formState: { errors } } = useForm<LibraryFormValues>({
    resolver: zodResolver(librarySchema),
    defaultValues: {
      libraryCategoryId: initial?.libraryCategoryId ?? "",
      title: initial?.title ?? "",
      description: initial?.description ?? "",
      fileMediaId: initial?.file?.id ?? "",
      externalUrl: initial?.externalUrl ?? "",
      visibility: initial?.visibility ?? "internal",
    },
  });

  const { field: fileField } = useController({ name: "fileMediaId", control });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <CategorySelect endpoint="/admin/library-categories" label="Kategori" {...register("libraryCategoryId")} error={errors.libraryCategoryId?.message} />
      <div>
        <Label htmlFor="title">Judul</Label>
        <Input id="title" {...register("title")} error={errors.title?.message} />
      </div>
      <div>
        <Label htmlFor="description">Deskripsi</Label>
        <Textarea id="description" {...register("description")} error={errors.description?.message} />
      </div>

      {/* File atau URL Eksternal — backend validates exactly one is provided */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <MediaReferenceField
          collection="library-file"
          label="File Dokumen"
          currentMedia={initial?.file ?? null}
          onMediaChange={(id) => fileField.onChange(id ?? "")}
          onUploadingChange={setIsUploading}
          error={errors.fileMediaId?.message}
          disabled={loading}
        />
        <div>
          <Label htmlFor="externalUrl">URL Eksternal</Label>
          <Input id="externalUrl" {...register("externalUrl")} error={errors.externalUrl?.message} />
        </div>
      </div>

      <div>
        <Label htmlFor="visibility">Visibilitas</Label>
        <Select id="visibility" {...register("visibility")} options={[{ value: "internal", label: "Internal" }, { value: "public", label: "Publik" }]} error={errors.visibility?.message} />
      </div>
      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>Batal</Button>
        <Button type="submit" loading={loading} disabled={isUploading || loading}>Simpan</Button>
      </div>
    </form>
  );
}
