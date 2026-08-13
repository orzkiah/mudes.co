import { z } from "zod";

export const attendanceSessionSchema = z
  .object({
    sourceType: z.enum(["schedule_occurrence", "activity"]),
    sourceId: z.string().uuid("Sumber kegiatan wajib diisi"),
    opensAt: z.string().min(1, "Waktu buka wajib diisi"),
    closesAt: z.string().min(1, "Waktu tutup wajib diisi"),
  })
  .refine((value) => new Date(value.closesAt) > new Date(value.opensAt), {
    message: "Waktu tutup harus setelah waktu buka",
    path: ["closesAt"],
  });

export type AttendanceSessionFormValues = z.infer<typeof attendanceSessionSchema>;
