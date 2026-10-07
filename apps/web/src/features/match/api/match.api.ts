import { api } from '@/shared/api/client'
import type { MatchReport } from '../types/match.types'
import { z } from 'zod'

const uuid = (): string => crypto.randomUUID()

interface Collection<T> {
  data: T[]
  meta: { page: number; per_page: number; total: number }
  links: Record<string, string | null>
}

export interface PageResult<T> {
  items: T[]
  total: number
}

const ulid = z.string().regex(/^[0-9A-HJKMNP-TV-Z]{26}$/)
const skillMatchSchema = z
  .object({
    signal_id: z.string(),
    label: z.string(),
    importance: z.enum(['required', 'preferred']),
    evidence_level: z.enum(['strong', 'weak', 'missing']),
    source_references: z.array(z.string()),
  })
  .passthrough()

const matchReportSchema = z
  .object({
    id: ulid,
    cv_version_id: ulid,
    job_description_id: ulid,
    job_description_revision_id: ulid,
    analysis_id: ulid,
    analysis_rule_version: z.string(),
    matching_rule_version: z.string(),
    report_schema_version: z.string(),
    overall_score: z.number().finite().min(0).max(100),
    matched_skills: z.array(skillMatchSchema),
    missing_skills: z.array(skillMatchSchema),
    weak_evidence: z.array(skillMatchSchema),
    recommendations: z.array(
      z
        .object({
          id: z.string(),
          target_cv_section: z.string(),
          related_signal_ids: z.array(z.string()),
          rationale: z.string(),
          action: z.string(),
          priority: z.enum(['high', 'medium', 'low']),
        })
        .passthrough(),
    ),
    source_summary: z.object({
      company: z.string().nullable(),
      role: z.string().nullable(),
      revision_number: z.number().nullable(),
      source_deleted: z.boolean(),
      source_is_current: z.boolean(),
    }),
    created_at: z.string().nullable(),
  })
  .passthrough()

export async function getMatchReports(page = 1, perPage = 20): Promise<PageResult<MatchReport>> {
  const response = await api<Collection<MatchReport>>('/match-reports', {
    query: { page, per_page: perPage },
  })

  return {
    items: response.data.map((item) => matchReportSchema.parse(item) as MatchReport),
    total: response.meta.total,
  }
}

export async function createMatchReport(
  cvVersionId: string,
  jobDescriptionId: string,
  idempotencyKey = uuid(),
): Promise<MatchReport> {
  const response = await api<{ data: MatchReport }>('/match-reports', {
    method: 'POST',
    body: { cv_version_id: cvVersionId, job_description_id: jobDescriptionId },
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return matchReportSchema.parse(response.data) as MatchReport
}

export async function getMatchReport(id: string): Promise<MatchReport> {
  const response = await api<{ data: MatchReport }>(`/match-reports/${encodeURIComponent(id)}`)
  return matchReportSchema.parse(response.data) as MatchReport
}
