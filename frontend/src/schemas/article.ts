import { z } from "zod";

export const articleSchema = z.object({
  articleCategoryId: z.string().uuid("Kategori wajib dipilih"),
  title: z.string().min(1, "Judul wajib diisi").max(255),
  slug: z.string().max(280).optional(),
  excerpt: z.string().max(1000).optional(),
  body: z.string().min(1, "Isi artikel wajib diisi"),
  coverMediaId: z.string().uuid().optional().or(z.literal("")),
  status: z.enum(["draft", "scheduled", "published", "archived"]),
  publishedAt: z.string().optional(),
});

export type ArticleFormValues = z.infer<typeof articleSchema>;
