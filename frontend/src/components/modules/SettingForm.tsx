"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { settingSchema, type SettingFormValues } from "@/schemas/setting";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Select } from "@/components/ui/Select";
import { Switch } from "@/components/ui/Switch";
import { Textarea } from "@/components/ui/Textarea";
import type { Setting } from "@/types/models";

interface SettingFormProps {
  initial?: Setting | null;
  onSubmit: (values: SettingFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function SettingForm({ initial, onSubmit, onCancel, loading }: SettingFormProps) {
  const { register, handleSubmit, watch, formState: { errors } } = useForm<SettingFormValues>({
    resolver: zodResolver(settingSchema),
    defaultValues: {
      key: initial?.key ?? "",
      value: initial?.value ?? "",
      type: initial?.type ?? "string",
      group: initial?.group ?? "general",
      description: initial?.description ?? "",
      isEncrypted: initial?.isEncrypted ?? false,
    },
  });
  const type = watch("type");

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="key">Key</Label>
          <Input id="key" {...register("key")} error={errors.key?.message} />
        </div>
        <div>
          <Label htmlFor="group">Grup</Label>
          <Input id="group" {...register("group")} error={errors.group?.message} />
        </div>
      </div>
      <div>
        <Label htmlFor="type">Tipe</Label>
        <Select id="type" {...register("type")} options={[{ value: "string", label: "String" }, { value: "number", label: "Number" }, { value: "boolean", label: "Boolean" }, { value: "json", label: "JSON" }, { value: "encrypted", label: "Encrypted" }]} error={errors.type?.message} />
      </div>
      <div>
        <Label htmlFor="value">Value</Label>
        {type === "json" ? <Textarea id="value" {...register("value")} error={errors.value?.message} /> : <Input id="value" {...register("value")} error={errors.value?.message} />}
      </div>
      <div>
        <Label htmlFor="description">Deskripsi</Label>
        <Textarea id="description" {...register("description")} error={errors.description?.message} />
      </div>
      <Switch {...register("isEncrypted")} label="Nilai terenkripsi" />
      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>Batal</Button>
        <Button type="submit" loading={loading}>Simpan</Button>
      </div>
    </form>
  );
}
