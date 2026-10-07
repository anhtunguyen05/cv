import { api } from '@/shared/api/client'
import type { JdAnalysis, JobDescription } from '../types/jd.types'
import { z } from 'zod'

interface Collection<T> {
  data: T[]
  meta: { page: number; per_page: number; total: number }
  links: Record<string, string | null>
}

export interface PageResult<T> {
  items: T[]
  total: number
}

const uuid = (): string => crypto.randomUUID()
const ulid = z.string().regex(/^[0-9A-HJKMNP-TV-Z]{26}$/)
const signalState = z.enum(['detected', 'absent', 'unknown'])
const signalList = z.object({
  state: signalState,
  items: z.array(z.union([z.string(), z.object({ signal_id: z.string(), label: z.string() })])),
})
const signalValue = z.object({ state: signalState, value: z.string().nullable() })
const revisionSchema = z.object({
  id: ulid,
  job_description_id: ulid,
  revision_number: z.number().int().positive(),
  raw_text: z.string(),
  company: z.string().nullable(),
  role: z.string().nullable(),
  created_at: z.string().min(1),
})
const jobDescriptionSchema = z.object({
  id: ulid,
  company: z.string().nullable(),
  role: z.string().nullable(),
  current_revision: revisionSchema.nullable(),
  deleted_at: z.string().nullable(),
  created_at: z.string().min(1),
  updated_at: z.string().min(1),
})
const analysisSchema = z.object({
  id: ulid,
  job_description_revision_id: ulid,
  analysis_schema_version: z.literal('1.0.0'),
  analysis_rule_version: z.literal('1.0.0'),
  status: z.enum(['succeeded', 'failed', 'pending', 'running']),
  created_at: z.string().nullable(),
  signals: z.object({
    role: signalValue,
    required_skills: signalList,
    nice_to_have_skills: signalList,
    responsibilities: signalList,
    keywords: signalList,
    seniority: signalValue,
    soft_skills: signalList,
    domain_context: signalList,
  }),
})

export async function getJobDescriptions(
  page = 1,
  perPage = 20,
): Promise<PageResult<JobDescription>> {
  const response = await api<Collection<JobDescription>>('/job-descriptions', {
    query: { page, per_page: perPage },
  })
  return {
    items: response.data.map((item) => jobDescriptionSchema.parse(item) as JobDescription),
    total: response.meta.total,
  }
}

export async function getJobDescription(id: string): Promise<JobDescription> {
  const response = await api<{ data: JobDescription }>(
    `/job-descriptions/${encodeURIComponent(id)}`,
  )
  return jobDescriptionSchema.parse(response.data) as JobDescription
}

export async function createJobDescription(
  data: {
    raw_text: string
    company?: string | null
    role?: string | null
  },
  idempotencyKey = uuid(),
): Promise<JobDescription> {
  const response = await api<{ data: JobDescription }>('/job-descriptions', {
    method: 'POST',
    body: data,
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return jobDescriptionSchema.parse(response.data) as JobDescription
}

export async function updateJobDescription(
  id: string,
  revisionId: string,
  data: {
    raw_text?: string
    company?: string | null
    role?: string | null
  },
  idempotencyKey = uuid(),
): Promise<JobDescription> {
  const response = await api<{ data: JobDescription }>(
    `/job-descriptions/${encodeURIComponent(id)}`,
    {
      method: 'PATCH',
      body: data,
      headers: { 'If-Match': `"${revisionId}"`, 'Idempotency-Key': idempotencyKey },
    },
  )
  return jobDescriptionSchema.parse(response.data) as JobDescription
}

export async function deleteJobDescription(
  id: string,
  revisionId: string,
  idempotencyKey = uuid(),
): Promise<void> {
  await api(`/job-descriptions/${encodeURIComponent(id)}`, {
    method: 'DELETE',
    headers: { 'If-Match': `"${revisionId}"`, 'Idempotency-Key': idempotencyKey },
  })
}

export async function analyzeJobDescription(
  id: string,
  idempotencyKey = uuid(),
): Promise<JdAnalysis> {
  const response = await api<{ data: JdAnalysis }>(
    `/job-descriptions/${encodeURIComponent(id)}/analyses`,
    {
      method: 'POST',
      headers: { 'Idempotency-Key': idempotencyKey },
    },
  )
  return analysisSchema.parse(response.data) as JdAnalysis
}

export async function getJobDescriptionAnalysis(
  id: string,
  analysisId: string,
): Promise<JdAnalysis> {
  const response = await api<{ data: JdAnalysis }>(
    `/job-descriptions/${encodeURIComponent(id)}/analyses/${encodeURIComponent(analysisId)}`,
  )

  return analysisSchema.parse(response.data) as JdAnalysis
}
