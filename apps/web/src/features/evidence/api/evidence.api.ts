import { z } from 'zod'
import { api } from '@/shared/api/client'
import type { EvidenceInterview } from '../types/evidence.types'

const uuid = (): string => crypto.randomUUID()
const ulid = z.string().regex(/^[0-9A-HJKMNP-TV-Z]{26}$/)
const isoDateTime = z.string().datetime({ offset: true })

const areaSchema = z.object({
  signal_id: z.string().trim().min(1),
  label: z.string().trim().min(1),
  importance: z.enum(['required', 'preferred']),
  evidence_level: z.enum(['missing', 'weak']),
  source_references: z.array(z.string().trim().min(1)),
})

const questionSchema = z.object({
  id: ulid,
  area_signal_id: z.string().trim().min(1),
  question_version: z.literal('1.0'),
  question: z.string().trim().min(1),
})

export const evidenceInterviewSchema = z.object({
  id: ulid,
  match_report_id: ulid,
  cv_version_id: ulid,
  job_description_id: ulid,
  job_description_revision_id: ulid,
  analysis_id: ulid,
  areas: z.array(areaSchema).max(5),
  questions: z.array(questionSchema).max(5),
  question_set_version: z.literal('1.0'),
  status: z.enum(['active', 'completed', 'expired']),
  expires_at: isoDateTime,
  created_at: isoDateTime,
  updated_at: isoDateTime,
}).superRefine((interview, context) => {
  if (interview.areas.length !== interview.questions.length) {
    context.addIssue({ code: z.ZodIssueCode.custom, message: 'Each interview area must have exactly one question.' })
  }

  const areaIds = new Set(interview.areas.map((area) => area.signal_id))
  const questionAreaIds = new Set(interview.questions.map((question) => question.area_signal_id))
  if (areaIds.size !== interview.areas.length || questionAreaIds.size !== interview.questions.length) {
    context.addIssue({ code: z.ZodIssueCode.custom, message: 'Interview areas and questions must be unique.' })
  }
  for (const question of interview.questions) {
    if (!areaIds.has(question.area_signal_id)) {
      context.addIssue({ code: z.ZodIssueCode.custom, message: 'Every interview question must reference a stored area.' })
      break
    }
  }
})

export async function startEvidenceInterview(
  matchReportId: string,
  idempotencyKey = uuid(),
): Promise<EvidenceInterview> {
  const response = await api<{ data: EvidenceInterview }>(
    `/match-reports/${encodeURIComponent(matchReportId)}/evidence-interviews`,
    {
      method: 'POST',
      headers: { 'Idempotency-Key': idempotencyKey },
    },
  )

  return evidenceInterviewSchema.parse(response.data) as EvidenceInterview
}

export async function getEvidenceInterview(interviewId: string): Promise<EvidenceInterview> {
  const response = await api<{ data: EvidenceInterview }>(
    `/evidence-interviews/${encodeURIComponent(interviewId)}`,
  )

  return evidenceInterviewSchema.parse(response.data) as EvidenceInterview
}
