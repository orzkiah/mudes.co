"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { announcementSchema, type AnnouncementFormValues } from "@/schemas/announcement";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Select } from "@/components/ui/Select";
import { Switch } from "@/components/ui/Switch";
import { Textarea } from "@/components/ui/Textarea";
import type { Announcement } from "@/types/models";

function toLocalDateTime(value: string | null | undefined) {
  return value ? value.slice(0, 16) : "";
}

interface AnnouncementFormProps {
  initial?: Announcement | null;
  onSubmit: (values: AnnouncementFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function AnnouncementForm({ initial, onSubmit, onCancel, loading }: AnnouncementFormProps) {
  const { register, handleSubmit, formState: { errors } } = useForm<AnnouncementFormValues>({
    resolver: zodResolver(announcementSchema),
    defaultValues: {
      title: initial?.title ?? "",
      body: initial?.body ?? "",
      priority: initial?.priority ?? "normal",
      audience: initial?.audience ?? "public",
      pinned: initial?.pinned ?? false,
      startsAt: toLocalDateTime(initial?.startsAt) || new Date().toISOString().slice(0, 16),
      expiresAt: toLocalDateTime(initial?.expiresAt),
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="title">Judul</Label>
        <Input id="title" {...register("title")} error={errors.title?.message} />
      </div>
      <div>
        <Label htmlFor="body">Isi</Label>
        <Textarea id="body" {...register("body")} error={errors.body?.message} className="min-h-[140px]" />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="priority">Prioritas</Label>
          <Select id="priority" {...register("priority")} options={[{ value: "normal", label: "Normal" }, { value: "urgent", label: "Urgent" }]} error={errors.priority?.message} />
        </div>
        <div>
          <Label htmlFor="audience">Audiens</Label>
          <Select id="audience" {...register("audience")} options={[{ value: "public", label: "Publik" }, { value: "internal", label: "Internal" }]} error={errors.audience?.message} />
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="startsAt">Mulai</Label>
          <Input id="startsAt" type="datetime-local" {...register("startsAt")} error={errors.startsAt?.message} />
        </div>
        <div>
          <Label htmlFor="expiresAt">Berakhir</Label>
          <Input id="expiresAt" type="datetime-local" {...register("expiresAt")} error={errors.expiresAt?.message} />
        </div>
      </div>
      <Switch {...register("pinned")} label="Sematkan pengumuman" />
      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>Batal</Button>
        <Button type="submit" loading={loading}>Simpan</Button>
      </div>
    </form>
  );
}
