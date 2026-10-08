import { z } from 'zod'

export const cvTemplateSchema = z.object({
  id: z.string().min(1),
  version: z.string().min(1),
  name: z.string(),
  description: z.string().nullable(),
  status: z.literal('active'),
  supported_sections: z.array(z.string()),
  preview_metadata: z
    .union([z.record(z.string(), z.unknown()), z.array(z.never()).length(0)])
    .transform((value) => (Array.isArray(value) ? {} : value)),
})

export type CvTemplate = z.infer<typeof cvTemplateSchema>
