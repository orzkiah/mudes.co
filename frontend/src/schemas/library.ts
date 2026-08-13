import { z } from "zod";

export const librarySchema = z
  .object({
    libraryCategoryId: z.string().uuid("Kategori wajib dipilih"),
    title: z.string().min(1, "Judul wajib diisi").max(255),
    description: z.string().max(2000).optional(),
    fileMediaId: z.string().uuid("ID media tidak valid").optional().or(z.literal("")),
    externalUrl: z.string().url("URL tidak valid").optional().or(z.literal("")),
    visibility: z.enum(["public", "internal"]),
  })
  .refine((value) => Boolean(value.fileMediaId) !== Boolean(value.externalUrl), {
    message: "Isi tepat salah satu: ID file atau URL eksternal",
    path: ["fileMediaId"],
  });

export type LibraryFormValues = z.infer<typeof librarySchema>;
