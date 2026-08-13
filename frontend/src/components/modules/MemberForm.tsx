"use client";

import { useState } from "react";
import { useForm, useController } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { memberSchema, type MemberFormValues } from "@/schemas/member";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import { DateInput } from "@/components/ui/DateInput";
import type { Member } from "@/types/models";

interface MemberFormProps {
  initial?: Member | null;
  onSubmit: (values: MemberFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function MemberForm({ initial, onSubmit, onCancel, loading }: MemberFormProps) {
  const [isUploading, setIsUploading] = useState(false);

  const {
    register,
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<MemberFormValues>({
    resolver: zodResolver(memberSchema),
    defaultValues: {
      fullName: initial?.fullName ?? "",
      gender: initial?.gender ?? "",
      birthDate: initial?.birthDate ?? "",
      phone: initial?.phone ?? "",
      joinDate: initial?.joinDate ?? "",
      status: initial?.status ?? "active",
      notes: initial?.notes ?? "",
      photoMediaId: initial?.photo?.id ?? "",
    },
  });

  const { field: photoField } = useController({ name: "photoMediaId", control });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="fullName">Nama Lengkap</Label>
        <Input id="fullName" {...register("fullName")} error={errors.fullName?.message} />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="gender">Jenis Kelamin</Label>
          <Select
            id="gender"
            {...register("gender")}
            options={[
              { value: "", label: "-" },
              { value: "male", label: "Laki-laki" },
              { value: "female", label: "Perempuan" },
            ]}
          />
        </div>
        <div>
          <Label htmlFor="status">Status</Label>
          <Select
            id="status"
            {...register("status")}
            options={[
              { value: "active", label: "Aktif" },
              { value: "inactive", label: "Nonaktif" },
              { value: "alumni", label: "Alumni" },
              { value: "moved_out", label: "Pindah" },
            ]}
            error={errors.status?.message}
          />
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="birthDate">Tanggal Lahir</Label>
          <DateInput id="birthDate" {...register("birthDate")} />
        </div>
        <div>
          <Label htmlFor="joinDate">Tanggal Bergabung</Label>
          <DateInput id="joinDate" {...register("joinDate")} />
        </div>
      </div>
      <div>
        <Label htmlFor="phone">Telepon</Label>
        <Input id="phone" {...register("phone")} placeholder="+6281234567890" error={errors.phone?.message} />
      </div>

      <MediaReferenceField
        collection="member-photo"
        label="Foto Anggota"
        currentMedia={initial?.photo ?? null}
        onMediaChange={(id) => photoField.onChange(id ?? "")}
        onUploadingChange={setIsUploading}
        error={errors.photoMediaId?.message}
        disabled={loading}
      />

      <div>
        <Label htmlFor="notes">Catatan</Label>
        <Textarea id="notes" {...register("notes")} error={errors.notes?.message} />
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
