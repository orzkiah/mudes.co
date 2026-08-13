"use client";

import { useState } from "react";
import { FileDropzone } from "./FileDropzone";
import { Alert } from "./Alert";
import { Button } from "./Button";
import { Spinner } from "./Spinner";
import { Label } from "./Label";
import { uploadMedia } from "@/lib/query-utils";
import { getApiErrorMessage } from "@/lib/query-client";
import { ImageCropperModal } from "./ImageCropperModal";
import type { MediaObject } from "@/lib/api-types";

// ─── Collection metadata ──────────────────────────────────────────────────────

export type MediaCollection =
  | "member-photo"
  | "article-cover"
  | "activity-cover"
  | "gallery-photo"
  | "library-file";

const COLLECTION_CONFIG: Record<
  MediaCollection,
  { accept: string; label: string; isImage: boolean; hint: string }
> = {
  "member-photo": {
    accept: "image/jpeg,image/png,image/webp",
    label: "Foto",
    isImage: true,
    hint: "JPG, PNG, WebP — maks. 10 MB",
  },
  "article-cover": {
    accept: "image/jpeg,image/png,image/webp",
    label: "Gambar Cover",
    isImage: true,
    hint: "JPG, PNG, WebP — maks. 10 MB",
  },
  "activity-cover": {
    accept: "image/jpeg,image/png,image/webp",
    label: "Gambar Cover",
    isImage: true,
    hint: "JPG, PNG, WebP — maks. 10 MB",
  },
  "gallery-photo": {
    accept: "image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime",
    label: "Foto Cover Album",
    isImage: true,
    hint: "JPG, PNG, WebP, MP4, WebM — maks. 100 MB",
  },
  "library-file": {
    accept: "application/pdf,audio/mpeg,video/mp4",
    label: "File Dokumen",
    isImage: false,
    hint: "PDF, MP3, MP4 — maks. 100 MB",
  },
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatBytes(bytes: number | undefined): string {
  if (bytes === undefined || bytes === null) return "—";
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function isImageMime(mimeType: string | undefined): boolean {
  return typeof mimeType === "string" && mimeType.startsWith("image/");
}

// ─── Component ───────────────────────────────────────────────────────────────

interface MediaReferenceFieldProps {
  /** One of the five valid backend collection names. */
  collection: MediaCollection;
  /** Outer label shown above the dropzone. */
  label: string;
  /** Media already saved on the entity (for edit mode). */
  currentMedia?: MediaObject | null;
  /** Called when a new upload completes (passes media.id and media.url) or user removes media (passes null). */
  onMediaChange: (mediaId: string | null, mediaUrl?: string | null) => void;
  /** Called when the upload state changes so the parent can disable submit. */
  onUploadingChange?: (isUploading: boolean) => void;
  /** Validation error from React Hook Form. */
  error?: string;
  /** Whether the entire form is disabled (e.g. form mutation pending). */
  disabled?: boolean;
}

export function MediaReferenceField({
  collection,
  label,
  currentMedia,
  onMediaChange,
  onUploadingChange,
  error,
  disabled = false,
}: MediaReferenceFieldProps) {
  const config = COLLECTION_CONFIG[collection];

  // uploadedMedia: the result of a *new* upload in this session.
  // If null, currentMedia (from props) is the active media.
  const [uploadedMedia, setUploadedMedia] = useState<MediaObject | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  // Local object URL for instant image preview before the backend URL resolves.
  const [localPreviewUrl, setLocalPreviewUrl] = useState<string | null>(null);
  // Whether user explicitly cleared the media (set mediaId to null/empty).
  const [cleared, setCleared] = useState(false);

  // Image Cropper States
  const [cropperOpen, setCropperOpen] = useState(false);
  const [cropperSrc, setCropperSrc] = useState<string | null>(null);
  const [cropperFileName, setCropperFileName] = useState("");

  // The "active" media to display: prefer newly-uploaded, then fall back to
  // currentMedia (edit mode), unless user has explicitly cleared.
  const activeMedia = cleared ? null : (uploadedMedia ?? currentMedia ?? null);
  const previewUrl = localPreviewUrl ?? (activeMedia && isImageMime(activeMedia.mimeType) ? activeMedia.url : null);

  const handleFileSelect = async (file: File) => {
    // Client-side size guard — 100 MB for gallery/library, 10 MB for photos
    const maxMb = (collection === "gallery-photo" || collection === "library-file") ? 100 : 10;
    if (file.size > maxMb * 1024 * 1024) {
      setUploadError(`Ukuran file melebihi batas maksimum ${maxMb} MB.`);
      return;
    }

    if (config.isImage && collection === "member-photo") {
      // Load image source as DataURL to open in ImageCropperModal (only for member avatar photos)
      const reader = new FileReader();
      reader.onload = () => {
        setCropperSrc(reader.result as string);
        setCropperFileName(file.name);
        setCropperOpen(true);
      };
      reader.readAsDataURL(file);
    } else {
      // Non-image files and cover photos bypass cropping
      await executeUpload(file);
    }
  };

  const executeUpload = async (file: File) => {
    // Revoke any previous local preview URL to avoid memory leaks.
    if (localPreviewUrl) URL.revokeObjectURL(localPreviewUrl);

    // Show a local preview immediately for images while the upload is in flight.
    if (config.isImage) {
      setLocalPreviewUrl(URL.createObjectURL(file));
    }

    setUploadError(null);
    setIsUploading(true);
    onUploadingChange?.(true);

    try {
      const media = await uploadMedia(file, collection);
      setUploadedMedia(media);
      setCleared(false);
      onMediaChange(media.id, media.url);
    } catch (err) {
      // On error: clear the local preview so the UI does not show a stale image.
      if (localPreviewUrl) {
        URL.revokeObjectURL(localPreviewUrl);
        setLocalPreviewUrl(null);
      }
      setUploadError(getApiErrorMessage(err));
      // Do not call onMediaChange — keep the previous media ID in the form.
    } finally {
      setIsUploading(false);
      onUploadingChange?.(false);
    }
  };

  const handleClear = () => {
    if (localPreviewUrl) URL.revokeObjectURL(localPreviewUrl);
    setLocalPreviewUrl(null);
    setUploadedMedia(null);
    setUploadError(null);
    setCleared(true);
    onMediaChange("");
  };

  // ─── Render: active media (uploaded or existing) ──────────────────────────
  if (activeMedia) {
    const showImage = isImageMime(activeMedia.mimeType);
    const displayUrl = localPreviewUrl ?? activeMedia.url;

    return (
      <>
        <div className="space-y-2">
          <Label>{label}</Label>
          <div className="rounded-xl border border-outline-variant bg-surface-container-lowest p-4">
            <div className="flex items-start gap-4">
              {/* Thumbnail / file icon */}
              {showImage ? (
                <img
                  src={displayUrl}
                  alt={activeMedia.name}
                  className="h-20 w-20 shrink-0 rounded-lg object-cover bg-surface-container"
                />
              ) : (
                <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-surface-container">
                  <span className="material-symbols-outlined text-4xl text-on-surface-variant">
                    {(activeMedia.mimeType ?? "").includes("pdf")
                      ? "picture_as_pdf"
                      : (activeMedia.mimeType ?? "").includes("audio")
                      ? "audio_file"
                      : "insert_drive_file"}
                  </span>
                </div>
              )}

              {/* File info */}
              <div className="min-w-0 flex-1">
                <p className="text-body-md font-body-md text-on-surface truncate">
                  {activeMedia.fileName ?? activeMedia.name}
                </p>
                <p className="text-label-sm text-on-surface-variant mt-0.5">
                  {activeMedia.mimeType} · {formatBytes(activeMedia.size)}
                </p>
                {isUploading && (
                  <div className="mt-2 flex items-center gap-2 text-label-sm text-primary">
                    <Spinner className="w-3 h-3" />
                    <span>Mengunggah…</span>
                  </div>
                )}
              </div>

              {/* Actions */}
              {!disabled && (
                <div className="flex shrink-0 flex-col gap-1">
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      // Reset to dropzone state so user can pick a new file.
                      if (localPreviewUrl) URL.revokeObjectURL(localPreviewUrl);
                      setLocalPreviewUrl(null);
                      setUploadedMedia(null);
                      setCleared(false);
                      setUploadError(null);
                      // Keep the current media ID in form until a new upload succeeds.
                    }}
                    disabled={isUploading}
                  >
                    Ganti
                  </Button>
                  <Button
                    type="button"
                    size="sm"
                    variant="ghost"
                    onClick={handleClear}
                    disabled={isUploading}
                  >
                    Hapus
                  </Button>
                </div>
              )}
            </div>
          </div>

          {error && <p className="text-label-sm text-error">{error}</p>}
        </div>

        <ImageCropperModal
          isOpen={cropperOpen}
          onClose={() => {
            setCropperOpen(false);
            setCropperSrc(null);
          }}
          imageSrc={cropperSrc}
          fileName={cropperFileName}
          isCircular={collection === "member-photo"}
          aspectRatio={collection === "member-photo" ? 1 : 16 / 9}
          onCropComplete={(file) => {
            executeUpload(file);
          }}
        />
      </>
    );
  }

  // ─── Render: dropzone (no active media) ───────────────────────────────────
  return (
    <>
      <div className="space-y-2">
        <Label>{label}</Label>

        {isUploading ? (
          <div className="flex items-center justify-center gap-3 rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-lowest p-8">
            <Spinner className="w-5 h-5 text-primary" />
            <span className="text-body-md text-on-surface-variant">Mengunggah file…</span>
          </div>
        ) : (
          <FileDropzone
            accept={config.accept}
            onFileSelect={handleFileSelect}
            previewUrl={previewUrl}
            className={disabled ? "pointer-events-none opacity-50" : undefined}
          />
        )}

        <p className="text-label-sm text-on-surface-variant">{config.hint}</p>

        {uploadError && (
          <Alert variant="error">{uploadError}</Alert>
        )}

        {error && <p className="text-label-sm text-error">{error}</p>}
      </div>

      <ImageCropperModal
        isOpen={cropperOpen}
        onClose={() => {
          setCropperOpen(false);
          setCropperSrc(null);
        }}
        imageSrc={cropperSrc}
        fileName={cropperFileName}
        isCircular={collection === "member-photo"}
        aspectRatio={collection === "member-photo" ? 1 : 16 / 9}
        onCropComplete={(file) => {
          executeUpload(file);
        }}
      />
    </>
  );
}
