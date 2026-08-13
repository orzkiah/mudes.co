"use client";

import { useState } from "react";
import { useForm, useController } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { activitySchema, type ActivityFormValues } from "@/schemas/activity";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { dayjs } from "@/lib/dates";
import type { Activity } from "@/types/models";

interface ActivityFormProps {
  initial?: Activity | null;
  onSubmit: (values: ActivityFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

const statusOptions = [
  { value: "upcoming", label: "Akan datang" },
  { value: "ongoing", label: "Berlangsung" },
  { value: "completed", label: "Selesai" },
  { value: "cancelled", label: "Dibatalkan" },
];

export function ActivityForm({ initial, onSubmit, onCancel, loading }: ActivityFormProps) {
  const [isUploading, setIsUploading] = useState(false);

  const {
    register,
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<ActivityFormValues>({
    resolver: zodResolver(activitySchema),
    defaultValues: {
      activityCategoryId: initial?.activityCategoryId ?? "",
      title: initial?.title ?? "",
      slug: initial?.slug ?? "",
      description: initial?.description ?? "",
      startAt: initial?.startAt ? dayjs(initial.startAt).format("YYYY-MM-DDTHH:mm") : "",
      endAt: initial?.endAt ? dayjs(initial.endAt).format("YYYY-MM-DDTHH:mm") : "",
      location: initial?.location ?? "",
      status: initial?.status ?? "upcoming",
      coverMediaId: initial?.cover?.id ?? "",
    },
  });

  const { field: coverField } = useController({ name: "coverMediaId", control });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <CategorySelect
        label="Kategori Kegiatan"
        endpoint="/admin/activity-categories"
        {...register("activityCategoryId")}
        error={errors.activityCategoryId?.message}
      />
      <div>
        <Label htmlFor="title">Judul</Label>
        <Input id="title" {...register("title")} error={errors.title?.message} />
      </div>
      <div>
        <Label htmlFor="slug">Slug (opsional)</Label>
        <Input id="slug" {...register("slug")} error={errors.slug?.message} />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="startAt">Mulai</Label>
          <Input id="startAt" type="datetime-local" {...register("startAt")} error={errors.startAt?.message} />
        </div>
        <div>
          <Label htmlFor="endAt">Selesai</Label>
          <Input id="endAt" type="datetime-local" {...register("endAt")} error={errors.endAt?.message} />
        </div>
      </div>
      <div>
        <Label htmlFor="location">Lokasi</Label>
        <Input id="location" {...register("location")} error={errors.location?.message} />
      </div>
      <div>
        <Label htmlFor="status">Status</Label>
        <Select id="status" {...register("status")} options={statusOptions} error={errors.status?.message} />
      </div>

      <MediaReferenceField
        collection="activity-cover"
        label="Gambar Cover"
        currentMedia={initial?.cover ?? null}
        onMediaChange={(id) => coverField.onChange(id ?? "")}
        onUploadingChange={setIsUploading}
        error={errors.coverMediaId?.message}
        disabled={loading}
      />

      <div>
        <Label htmlFor="description">Deskripsi</Label>
        <Textarea id="description" {...register("description")} error={errors.description?.message} />
      </div>
      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>
          Batal
        </Button>
        <Button type="submit" loading={loading} disabled={isUploading || loading}>
          Simpan
        </Button>
      </div>
    </form>
  );
}
