"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { taxonomySchema, type TaxonomyFormValues } from "@/schemas/taxonomy";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Textarea } from "@/components/ui/Textarea";
import { Switch } from "@/components/ui/Switch";
import type { Taxonomy } from "@/lib/api-types";

interface TaxonomyFormProps {
  initial?: Taxonomy | null;
  onSubmit: (values: TaxonomyFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function TaxonomyForm({ initial, onSubmit, onCancel, loading }: TaxonomyFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<TaxonomyFormValues>({
    resolver: zodResolver(taxonomySchema),
    defaultValues: {
      name: initial?.name ?? "",
      slug: initial?.slug ?? "",
      description: initial?.description ?? "",
      icon: initial?.icon ?? "",
      color: initial?.color ?? "",
      displayOrder: initial?.displayOrder ?? undefined,
      isActive: initial?.isActive ?? true,
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="name">Nama</Label>
        <Input id="name" {...register("name")} error={errors.name?.message} />
      </div>
      <div>
        <Label htmlFor="slug">Slug (opsional)</Label>
        <Input id="slug" {...register("slug")} error={errors.slug?.message} />
      </div>
      <div>
        <Label htmlFor="description">Deskripsi</Label>
        <Textarea id="description" {...register("description")} error={errors.description?.message} />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <Label htmlFor="icon">Ikon (opsional)</Label>
          <Input id="icon" {...register("icon")} placeholder="contoh: mosque" error={errors.icon?.message} />
        </div>
        <div>
          <Label htmlFor="color">Warna (opsional)</Label>
          <div className="flex gap-2">
            <Input id="color" {...register("color")} placeholder="#003527" error={errors.color?.message} />
          </div>
        </div>
      </div>
      <div>
        <Label htmlFor="displayOrder">Urutan Tampil</Label>
        <Input id="displayOrder" type="number" {...register("displayOrder")} error={errors.displayOrder?.message} />
      </div>
      <div className="pt-2">
        <Switch {...register("isActive")} label="Aktif" />
      </div>
      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>
          Batal
        </Button>
        <Button type="submit" loading={loading}>
          Simpan
        </Button>
      </div>
    </form>
  );
}
