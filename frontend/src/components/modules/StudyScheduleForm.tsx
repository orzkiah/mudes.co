"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { studyScheduleSchema, type StudyScheduleFormValues } from "@/schemas/studySchedule";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { Switch } from "@/components/ui/Switch";
import { TimeInput } from "@/components/ui/TimeInput";
import { CategorySelect } from "@/components/ui/CategorySelect";
import type { StudySchedule } from "@/types/models";

interface StudyScheduleFormProps {
  initial?: StudySchedule | null;
  onSubmit: (values: StudyScheduleFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

const DAY_NAMES = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

export function StudyScheduleForm({ initial, onSubmit, onCancel, loading }: StudyScheduleFormProps) {
  // Compute initial date string (prefer stored scheduledDate)
  const defaultDateStr = () => {
    if (initial?.scheduledDate) {
      return initial.scheduledDate;
    }
    const d = new Date();
    if (initial && typeof initial.dayOfWeek === "number") {
      const currentDay = d.getDay();
      let daysUntil = (initial.dayOfWeek - currentDay + 7) % 7;
      d.setDate(d.getDate() + daysUntil);
    }
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const dd = String(d.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  };

  const [selectedDate, setSelectedDate] = useState<string>(defaultDateStr());

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors },
  } = useForm<StudyScheduleFormValues>({
    resolver: zodResolver(studyScheduleSchema),
    defaultValues: {
      studyCategoryId: initial?.studyCategoryId ?? "",
      scheduledDate: initial?.scheduledDate ?? defaultDateStr(),
      dayOfWeek: initial?.dayOfWeek ?? new Date().getDay(),
      startTime: initial?.startTime ? initial.startTime.slice(0, 5) : "",
      endTime: initial?.endTime ? initial.endTime.slice(0, 5) : "",
      topic: initial?.topic ?? "",
      ustadzName: initial?.ustadzName ?? "",
      location: initial?.location ?? "",
      isActive: initial?.isActive ?? true,
    },
  });

  const getDayLabel = (dateStr: string) => {
    if (!dateStr) return "";
    const parts = dateStr.split("-").map(Number);
    if (parts.length !== 3) return "";
    const d = new Date(parts[0], parts[1] - 1, parts[2]);
    return DAY_NAMES[d.getDay()] ?? "";
  };

  const handleDateChange = (dateVal: string) => {
    setSelectedDate(dateVal);
    setValue("scheduledDate", dateVal);
    if (dateVal) {
      const parts = dateVal.split("-").map(Number);
      if (parts.length === 3) {
        const d = new Date(parts[0], parts[1] - 1, parts[2]);
        const dow = d.getDay();
        setValue("dayOfWeek", dow, { shouldValidate: true });
      }
    }
  };

  const handleFormSubmit = (values: StudyScheduleFormValues) => {
    values.scheduledDate = selectedDate;
    if (selectedDate) {
      const parts = selectedDate.split("-").map(Number);
      if (parts.length === 3) {
        const d = new Date(parts[0], parts[1] - 1, parts[2]);
        values.dayOfWeek = d.getDay();
      }
    }
    onSubmit(values);
  };

  return (
    <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-4">
      <CategorySelect
        label="Kategori Kajian *"
        endpoint="/admin/study-categories"
        {...register("studyCategoryId")}
        error={errors.studyCategoryId?.message}
      />

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="scheduledDate">Tanggal Pelaksanaan *</Label>
          <Input
            id="scheduledDate"
            type="date"
            value={selectedDate}
            onChange={(e) => handleDateChange(e.target.value)}
          />
          {selectedDate && (
            <p className="mt-1.5 text-label-sm font-semibold text-[#004b36] flex items-center gap-1">
              <span className="material-symbols-outlined text-[14px]">event</span>
              Hari pelaksanaan: <strong className="font-bold uppercase">{getDayLabel(selectedDate)}</strong>
            </p>
          )}
        </div>
        <div>
          <Label htmlFor="ustadzName">Ustadz / Pemateri *</Label>
          <Input id="ustadzName" {...register("ustadzName")} error={errors.ustadzName?.message} />
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="startTime">Waktu Mulai *</Label>
          <TimeInput id="startTime" {...register("startTime")} error={errors.startTime?.message} />
        </div>
        <div>
          <Label htmlFor="endTime">Waktu Selesai *</Label>
          <TimeInput id="endTime" {...register("endTime")} error={errors.endTime?.message} />
        </div>
      </div>

      <div>
        <Label htmlFor="topic">Topik / Nama Kajian *</Label>
        <Input id="topic" {...register("topic")} error={errors.topic?.message} />
      </div>

      <div>
        <Label htmlFor="location">Lokasi</Label>
        <Input id="location" {...register("location")} error={errors.location?.message} />
      </div>

      <div className="pt-2">
        <Switch {...register("isActive")} label="Status Aktif" />
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
