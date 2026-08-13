import { z } from "zod";

export const settingSchema = z.object({
  key: z.string().min(1, "Key wajib diisi").max(150),
  value: z.union([z.string(), z.number(), z.boolean()]).optional().or(z.literal("")),
  type: z.enum(["string", "number", "boolean", "json", "encrypted"]),
  group: z.string().max(50).optional(),
  description: z.string().max(1000).optional(),
  isEncrypted: z.boolean(),
});

export type SettingFormValues = z.infer<typeof settingSchema>;
