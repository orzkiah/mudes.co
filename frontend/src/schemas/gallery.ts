import { z } from "zod";

export const gallerySchema = z.object({
  galleryCategoryId: z.string().uuid("Kategori wajib dipilih"),
  activityId: z.string().uuid().optional().or(z.literal("")),
  title: z.string().min(1, "Judul wajib diisi").max(150),
  description: z.string().max(2000).optional(),
  coverPhotoMediaId: z.string().uuid().optional().or(z.literal("")),
});

export type GalleryFormValues = z.infer<typeof gallerySchema>;
