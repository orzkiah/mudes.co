import { z } from "zod";

export const studyScheduleSchema = z.object({
  studyCategoryId: z.string().uuid("Kategori wajib dipilih"),
  scheduledDate: z.string().optional(),
  dayOfWeek: z.coerce.number().int().min(0, "Hari tidak valid").max(6, "Hari tidak valid"),
  startTime: z.string().regex(/^([01]\d|2[0-3]):([0-5]\d)$/, "Format waktu HH:MM"),
  endTime: z.string().regex(/^([01]\d|2[0-3]):([0-5]\d)$/, "Format waktu HH:MM"),
  topic: z.string().max(150).optional(),
  ustadzName: z.string().min(1, "Nama ustadz wajib diisi").max(150),
  location: z.string().max(150).optional(),
  isActive: z.boolean().optional(),
});

export type StudyScheduleFormValues = z.infer<typeof studyScheduleSchema>;
