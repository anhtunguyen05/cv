import { z } from 'zod'

const ulid = z.string().regex(/^[0-9A-HJKMNP-TV-Z]{26}$/)
const signalState = z.enum(['detected', 'absent', 'unknown'])
const signalItemSchema = z.union([
  z.string(),
  z.object({ signal_id: z.string(), label: z.string() }),
])

const signalListSchema = z.object({
  state: signalState,
  items: z.array(signalItemSchema),
})

const signalValueSchema = z.object({
  state: signalState,
  value: z.string().nullable(),
})

export const jobDescriptionRevisionSchema = z.object({
  id: ulid,
  job_description_id: ulid,
  revision_number: z.number().int().positive(),
  raw_text: z.string(),
  company: z.string().nullable(),
  role: z.string().nullable(),
  created_at: z.string().min(1),
})

export const jobDescriptionSchema = z.object({
  id: ulid,
  company: z.string().nullable(),
  role: z.string().nullable(),
  current_revision: jobDescriptionRevisionSchema.nullable(),
  deleted_at: z.string().nullable(),
  created_at: z.string().min(1),
  updated_at: z.string().min(1),
})

export const jdAnalysisSchema = z.object({
  id: ulid,
  job_description_revision_id: ulid,
  analysis_schema_version: z.literal('1.0.0'),
  analysis_rule_version: z.literal('1.0.0'),
  status: z.enum(['succeeded', 'failed', 'pending', 'running']),
  created_at: z.string().nullable(),
  signals: z.object({
    role: signalValueSchema,
    required_skills: signalListSchema,
    nice_to_have_skills: signalListSchema,
    responsibilities: signalListSchema,
    keywords: signalListSchema,
    seniority: signalValueSchema,
    soft_skills: signalListSchema,
    domain_context: signalListSchema,
  }),
})

export type JobDescription = z.infer<typeof jobDescriptionSchema>
export type JobDescriptionRevision = z.infer<typeof jobDescriptionRevisionSchema>
export type JdAnalysis = z.infer<typeof jdAnalysisSchema>
export type SignalState = z.infer<typeof signalState>
export type SignalItem = z.infer<typeof signalItemSchema>
export interface SignalList<T = SignalItem> {
  state: SignalState
  items: T[]
}
export interface SignalValue<T = string> {
  state: SignalState
  value: T | null
}
