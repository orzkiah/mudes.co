import { z } from "zod";

export const activitySchema = z.object({
  activityCategoryId: z.string().uuid("Kategori wajib dipilih"),
  title: z.string().min(1, "Judul wajib diisi").max(150),
  slug: z.string().max(180).optional(),
  description: z.string().max(5000).optional(),
  startAt: z.string().min(1, "Waktu mulai wajib diisi"),
  endAt: z.string().optional(),
  location: z.string().max(150).optional(),
  status: z.enum(["upcoming", "ongoing", "completed", "cancelled"]),
  coverMediaId: z.string().uuid().optional().or(z.literal("")),
});

export type ActivityFormValues = z.infer<typeof activitySchema>;
