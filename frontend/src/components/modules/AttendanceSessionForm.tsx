"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { attendanceSessionSchema, type AttendanceSessionFormValues } from "@/schemas/attendanceSession";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Select } from "@/components/ui/Select";
import { ApiSelect } from "@/components/ui/ApiSelect";
import { formatDate } from "@/lib/dates";
import type { Activity, AttendanceSession, StudyScheduleOccurrence } from "@/types/models";

function toLocalDateTime(value: string | null | undefined) {
  return value ? value.slice(0, 16) : "";
}

interface AttendanceSessionFormProps {
  initial?: AttendanceSession | null;
  onSubmit: (values: AttendanceSessionFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

export function AttendanceSessionForm({ initial, onSubmit, onCancel, loading }: AttendanceSessionFormProps) {
  const {
    register,
    watch,
    setValue,
    handleSubmit,
    formState: { errors },
  } = useForm<AttendanceSessionFormValues>({
    resolver: zodResolver(attendanceSessionSchema),
    defaultValues: {
      sourceType: initial?.sourceType ?? "activity",
      sourceId: initial?.sourceId ?? "",
      opensAt: toLocalDateTime(initial?.opensAt),
      closesAt: toLocalDateTime(initial?.closesAt),
    },
  });

  const sourceType = watch("sourceType");
  const [prevSourceType, setPrevSourceType] = useState(sourceType);

  // Automatically reset sourceId when user changes sourceType to avoid stale UUIDs
  if (prevSourceType !== sourceType) {
    setPrevSourceType(sourceType);
    setValue("sourceId", "");
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="sourceType">Tipe Sumber Presensi</Label>
        <Select
          id="sourceType"
          {...register("sourceType")}
          options={[
            { value: "activity", label: "Kegiatan Komunitas" },
            { value: "schedule_occurrence", label: "Agenda Kajian (Occurrence)" },
          ]}
          error={errors.sourceType?.message}
        />
      </div>

      {sourceType === "activity" ? (
        <ApiSelect<Activity>
          id="sourceId"
          endpoint="/admin/activities"
          label="Kegiatan Komunitas"
          placeholder="Pilih kegiatan"
          getOptionLabel={(activity) =>
            `${activity.title}${activity.location ? ` — ${activity.location}` : ""}`
          }
          {...register("sourceId")}
          error={errors.sourceId?.message}
        />
      ) : (
        <ApiSelect<StudyScheduleOccurrence>
          id="sourceId"
          endpoint="/admin/schedule-occurrences"
          label="Occurrence Jadwal Kajian"
          placeholder="Pilih occurrence kajian"
          getOptionLabel={(occ) =>
            `${occ.schedule?.topic ?? "Kajian"} (${occ.schedule?.ustadzName ?? "Pemateri"}) — ${formatDate(occ.occurrenceDate)}`
          }
          {...register("sourceId")}
          error={errors.sourceId?.message}
        />
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="opensAt">Waktu Buka Presensi</Label>
          <Input
            id="opensAt"
            type="datetime-local"
            {...register("opensAt")}
            error={errors.opensAt?.message}
          />
        </div>
        <div>
          <Label htmlFor="closesAt">Waktu Tutup Presensi</Label>
          <Input
            id="closesAt"
            type="datetime-local"
            {...register("closesAt")}
            error={errors.closesAt?.message}
          />
        </div>
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
