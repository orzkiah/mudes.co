import { z } from "zod";

export const memberSchema = z.object({
  fullName: z.string().min(1, "Nama lengkap wajib diisi").max(150, "Maksimal 150 karakter"),
  gender: z.union([z.enum(["male", "female"]), z.literal("")]).optional(),
  birthDate: z.string().optional(),
  phone: z.string().max(20).optional(),
  joinDate: z.string().optional(),
  status: z.enum(["active", "inactive", "alumni", "moved_out"]),
  notes: z.string().max(1000).optional(),
  photoMediaId: z.string().uuid("ID media tidak valid").optional().or(z.literal("")),
});

export type MemberFormValues = z.infer<typeof memberSchema>;
