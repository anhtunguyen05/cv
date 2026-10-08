import { z } from 'zod'

export const paginationMetaSchema = z.object({
  page: z.number().int().positive().optional(),
  current_page: z.number().int().positive().optional(),
  per_page: z.number().int().positive(),
  total: z.number().int().nonnegative(),
  last_page: z.number().int().positive().optional(),
})

export const paginatedResponseSchema = z.object({
  data: z.array(z.unknown()),
  meta: paginationMetaSchema,
  links: z.record(z.string(), z.string().nullable()).optional(),
})
