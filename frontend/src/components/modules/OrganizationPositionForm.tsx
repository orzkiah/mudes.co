"use client";

import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { organizationPositionSchema, type OrganizationPositionFormValues } from "@/schemas/organizationPosition";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { ApiSelect } from "@/components/ui/ApiSelect";
import type { Member, OrganizationPeriod, OrganizationPosition } from "@/types/models";

interface OrganizationPositionFormProps {
  initial?: OrganizationPosition | null;
  onSubmit: (values: OrganizationPositionFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function OrganizationPositionForm({ initial, onSubmit, onCancel, loading }: OrganizationPositionFormProps) {
  // Fetch active period automatically behind the scenes
  const { data: periods } = useQuery<OrganizationPeriod[]>({
    queryKey: ["admin", "organization", "periods"],
    queryFn: async () => {
      const res = await api.get("/admin/organization/periods");
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  const activePeriodId = periods?.find((p) => p.isActive)?.id ?? periods?.[0]?.id ?? "";

  const { register, handleSubmit, setValue, watch, formState: { errors } } = useForm<OrganizationPositionFormValues>({
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    resolver: zodResolver(organizationPositionSchema) as any,
    defaultValues: {
      organizationPeriodId: initial?.organizationPeriodId ?? "",
      memberId: initial?.member?.id ?? "",
      title: initial?.title ?? "",
      positionType: initial?.positionType ?? "member",
      displayOrder: initial?.displayOrder ?? 1,
    },
  });

  // Automatically set periodId if not set
  useEffect(() => {
    if (!initial?.organizationPeriodId && activePeriodId) {
      setValue("organizationPeriodId", activePeriodId);
    }
  }, [activePeriodId, initial, setValue]);

  const titleValue = watch("title");

  const onCustomSubmit = (data: OrganizationPositionFormValues) => {
    // Auto-detect positionType from title if needed
    const lower = (data.title || "").toLowerCase();
    let posType: "chairman" | "vice_chairman" | "secretary" | "treasurer" | "coordinator" | "member" = "member";
    if (lower.includes("ketua umum") || lower.includes("ketua")) {
      posType = lower.includes("wakil") ? "vice_chairman" : "chairman";
    } else if (lower.includes("sekretaris")) {
      posType = "secretary";
    } else if (lower.includes("bendahara")) {
      posType = "treasurer";
    } else if (lower.includes("koordinator")) {
      posType = "coordinator";
    }

    const payload: OrganizationPositionFormValues = {
      ...data,
      organizationPeriodId: data.organizationPeriodId || activePeriodId,
      positionType: posType,
    };

    onSubmit(payload);
  };

  return (
    <form onSubmit={handleSubmit(onCustomSubmit)} className="space-y-4">
      {/* 1. Nama Pengurus (List Anggota) */}
      <ApiSelect<Member>
        id="memberId"
        endpoint="/admin/members"
        label="Nama Pengurus *"
        placeholder="Pilih nama anggota..."
        getOptionLabel={(member) => `${member.fullName} ${member.position?.title ? `— (${member.position.title})` : ""}`}
        {...register("memberId")}
        error={errors.memberId?.message}
      />

      {/* 2. Dapukan (Single Input Text) */}
      <div>
        <Label htmlFor="title">Dapukan / Jabatan *</Label>
        <Input
          id="title"
          placeholder="Ketik dapukan / jabatan (Contoh: Ketua Umum, Sekretaris, Koordinator Dakwah)"
          {...register("title")}
          error={errors.title?.message}
        />
      </div>

      {/* 3. Urutan Tampilan */}
      <div>
        <Label htmlFor="displayOrder">Urutan Tampilan *</Label>
        <Input
          id="displayOrder"
          type="number"
          min={1}
          placeholder="1"
          {...register("displayOrder")}
          error={errors.displayOrder?.message}
        />
      </div>

      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>
          Batal
        </Button>
        <Button type="submit" loading={loading}>
          Simpan Data
        </Button>
      </div>
    </form>
  );
}
