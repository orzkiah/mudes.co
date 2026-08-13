import { z } from "zod";

export const taxonomySchema = z.object({
  name: z.string().min(1, "Nama wajib diisi").max(100, "Maksimal 100 karakter"),
  slug: z.string().max(100).optional().transform((v) => (v === "" ? undefined : v)),
  description: z.string().max(500).optional().transform((v) => (v === "" ? undefined : v)),
  icon: z.string().max(100).optional().transform((v) => (v === "" ? undefined : v)),
  color: z
    .string()
    .regex(/^#[0-9A-Fa-f]{6}$/, "Format warna hex, contoh: #003527")
    .optional()
    .transform((v) => (v === "" ? undefined : v)),
  displayOrder: z.coerce.number().int().min(0).optional(),
  isActive: z.boolean().optional(),
});

export type TaxonomyFormValues = z.infer<typeof taxonomySchema>;
