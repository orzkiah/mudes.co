import { z } from "zod";

export const organizationPositionSchema = z.object({
  organizationPeriodId: z.string().optional().or(z.literal("")),
  departmentId: z.string().optional().or(z.literal("")),
  parentPositionId: z.string().optional().or(z.literal("")),
  memberId: z.string().min(1, "Nama pengurus wajib dipilih"),
  title: z.string().min(1, "Dapukan / Jabatan wajib diisi").max(150),
  positionType: z.enum(["chairman", "vice_chairman", "secretary", "treasurer", "coordinator", "member"]).default("member"),
  displayOrder: z.coerce.number().int().min(1, "Urutan minimal 1").default(1),
});

export type OrganizationPositionFormValues = z.infer<typeof organizationPositionSchema>;
