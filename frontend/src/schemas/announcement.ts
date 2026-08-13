import { z } from "zod";

export const announcementSchema = z
  .object({
    title: z.string().min(1, "Judul wajib diisi").max(255),
    body: z.string().min(1, "Isi pengumuman wajib diisi"),
    priority: z.enum(["normal", "urgent"]),
    audience: z.enum(["public", "internal"]),
    pinned: z.boolean(),
    startsAt: z.string().min(1, "Waktu mulai wajib diisi"),
    expiresAt: z.string().optional(),
  })
  .refine((value) => !value.expiresAt || new Date(value.expiresAt) > new Date(value.startsAt), {
    message: "Waktu berakhir harus setelah waktu mulai",
    path: ["expiresAt"],
  });

export type AnnouncementFormValues = z.infer<typeof announcementSchema>;
