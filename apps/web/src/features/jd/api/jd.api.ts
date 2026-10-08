import { api } from '@/shared/api/client'
import { paginatedResponseSchema } from '@/shared/schemas/pagination.schemas'
import { jdAnalysisSchema, jobDescriptionSchema } from '../schemas/jd.schemas'
import type { JdAnalysis, JobDescription } from '../types/jd.types'
import type { PageResult } from '@/shared/types/api.types'

const uuid = (): string => crypto.randomUUID()

export async function getJobDescriptions(
  page = 1,
  perPage = 20,
): Promise<PageResult<JobDescription>> {
  const response = paginatedResponseSchema.parse(
    await api<unknown>('/job-descriptions', {
      query: { page, per_page: perPage },
    }),
  )
  const responsePage = response.meta.page ?? response.meta.current_page ?? page

  return {
    items: response.data.map((item) => jobDescriptionSchema.parse(item)),
    page: responsePage,
    perPage: response.meta.per_page,
    lastPage:
      response.meta.last_page ??
      Math.max(1, Math.ceil(response.meta.total / response.meta.per_page)),
    total: response.meta.total,
  }
}

export async function getJobDescription(id: string): Promise<JobDescription> {
  const response = await api<{ data: unknown }>(`/job-descriptions/${encodeURIComponent(id)}`)
  return jobDescriptionSchema.parse(response.data)
}

export async function createJobDescription(
  data: {
    raw_text: string
    company?: string | null
    role?: string | null
  },
  idempotencyKey = uuid(),
): Promise<JobDescription> {
  const response = await api<{ data: unknown }>('/job-descriptions', {
    method: 'POST',
    body: data,
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return jobDescriptionSchema.parse(response.data)
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
  const response = await api<{ data: unknown }>(`/job-descriptions/${encodeURIComponent(id)}`, {
    method: 'PATCH',
    body: data,
    headers: { 'If-Match': `"${revisionId}"`, 'Idempotency-Key': idempotencyKey },
  })
  return jobDescriptionSchema.parse(response.data)
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
  const response = await api<{ data: unknown }>(
    `/job-descriptions/${encodeURIComponent(id)}/analyses`,
    {
      method: 'POST',
      headers: { 'Idempotency-Key': idempotencyKey },
    },
  )
  return jdAnalysisSchema.parse(response.data)
}

export async function getJobDescriptionAnalysis(
  id: string,
  analysisId: string,
): Promise<JdAnalysis> {
  const response = await api<{ data: unknown }>(
    `/job-descriptions/${encodeURIComponent(id)}/analyses/${encodeURIComponent(analysisId)}`,
  )

  return jdAnalysisSchema.parse(response.data)
}
