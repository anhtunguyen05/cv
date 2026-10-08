import { api } from '@/shared/api/client'
import { paginatedResponseSchema } from '@/shared/schemas/pagination.schemas'
import type { MatchReport } from '../types/match.types'
import type { PageResult } from '@/shared/types/api.types'
import { matchReportSchema } from '../schemas/match.schemas'

const uuid = (): string => crypto.randomUUID()

export async function getMatchReports(page = 1, perPage = 20): Promise<PageResult<MatchReport>> {
  const response = paginatedResponseSchema.parse(
    await api<unknown>('/match-reports', {
      query: { page, per_page: perPage },
    }),
  )
  const responsePage = response.meta.page ?? response.meta.current_page ?? page

  return {
    items: response.data.map((item) => matchReportSchema.parse(item)),
    page: responsePage,
    perPage: response.meta.per_page,
    lastPage:
      response.meta.last_page ??
      Math.max(1, Math.ceil(response.meta.total / response.meta.per_page)),
    total: response.meta.total,
  }
}

export async function createMatchReport(
  cvVersionId: string,
  jobDescriptionId: string,
  idempotencyKey = uuid(),
): Promise<MatchReport> {
  const response = await api<{ data: unknown }>('/match-reports', {
    method: 'POST',
    body: { cv_version_id: cvVersionId, job_description_id: jobDescriptionId },
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return matchReportSchema.parse(response.data)
}

export async function getMatchReport(id: string): Promise<MatchReport> {
  const response = await api<{ data: unknown }>(`/match-reports/${encodeURIComponent(id)}`)
  return matchReportSchema.parse(response.data)
}
