"use client";

import { useRef, useState } from "react";
import { cn } from "@/lib/cn";
import { Icon } from "./Icon";

interface FileDropzoneProps {
  accept?: string;
  onFileSelect: (file: File) => void;
  className?: string;
  previewUrl?: string | null;
}

export function FileDropzone({ accept, onFileSelect, className, previewUrl }: FileDropzoneProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [dragOver, setDragOver] = useState(false);

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(false);
    const file = e.dataTransfer.files?.[0];
    if (file) onFileSelect(file);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) onFileSelect(file);
  };

  return (
    <div
      onClick={() => inputRef.current?.click()}
      onDragOver={(e) => {
        e.preventDefault();
        setDragOver(true);
      }}
      onDragLeave={() => setDragOver(false)}
      onDrop={handleDrop}
      className={cn(
        "cursor-pointer rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-lowest p-6 text-center transition-colors hover:border-primary",
        dragOver && "border-primary bg-primary/5",
        className
      )}
    >
      <input
        ref={inputRef}
        type="file"
        accept={accept}
        className="hidden"
        onChange={handleChange}
      />
      {previewUrl ? (
        <img
          src={previewUrl}
          alt="Preview"
          className="mx-auto max-h-40 rounded-lg object-contain"
        />
      ) : (
        <>
          <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-surface-container">
            <Icon name="cloud_upload" className="text-2xl text-primary" />
          </div>
          <p className="text-body-md text-on-surface font-body-md">
            Klik atau seret file ke sini
          </p>
          <p className="text-body-sm text-on-surface-variant mt-1">
            {accept ? `Menerima: ${accept}` : "Maksimal 10 MB"}
          </p>
        </>
      )}
    </div>
  );
}
