import { z } from 'zod'
import { api } from '@/shared/api/client'
import type { EvidenceAnswer, EvidenceInterview, Patch } from '../types/evidence.types'

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
  answers: z.array(z.object({
    id: ulid,
    interview_id: ulid,
    question_id: ulid,
    question_version: z.literal('1.0'),
    area_signal_id: z.string().min(1),
    outcome: z.enum(['answer', 'cannot_provide']),
    answer: z.string().nullable(),
    provenance: z.literal('user'),
    created_at: isoDateTime,
  })).default([]),
  progress: z.object({ answered: z.number().int().nonnegative(), total: z.number().int().nonnegative(), remaining: z.number().int().nonnegative() }).default({ answered: 0, total: 0, remaining: 0 }),
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

const answerSchema = z.object({
  id: ulid,
  interview_id: ulid,
  question_id: ulid,
  question_version: z.literal('1.0'),
  area_signal_id: z.string().min(1),
  outcome: z.enum(['answer', 'cannot_provide']),
  answer: z.string().nullable(),
  provenance: z.literal('user'),
  created_at: isoDateTime,
})

const patchSchema = z.object({
  id: ulid,
  source_cv_version_id: ulid,
  match_report_id: ulid,
  interview_id: ulid,
  predecessor_patch_id: ulid.nullable(),
  status: z.enum(['pending_validation', 'pending', 'rejected', 'invalid', 'applied']),
  allowed_actions: z.array(z.enum(['edit', 'reject', 'approve', 'regenerate'])),
  revision: z.number().int().positive(),
  patch_schema_version: z.string().min(1),
  prompt_version: z.string().min(1),
  provider_model_version: z.string().min(1),
  target: z.object({ section: z.enum(['summary', 'experience', 'projects']), field: z.enum(['summary', 'highlights']), item_id: ulid.nullable(), operation: z.enum(['replace', 'append']), highlight_index: z.number().int().nonnegative().optional() }),
  old_value: z.union([z.string(), z.null(), z.object({ collection_hash: z.string() })]),
  new_value: z.string(),
  reason: z.string(),
  evidence_source_ids: z.array(ulid),
  provenance: z.record(z.string(), z.unknown()),
  applied_version_id: ulid.nullable(),
  created_at: isoDateTime,
  updated_at: isoDateTime,
  source: z.object({ id: ulid, snapshot: z.record(z.string(), z.unknown()) }).nullable().optional(),
  evidence: z.array(answerSchema).optional(),
  result_version: z.object({ id: ulid, source_cv_version_id: ulid, snapshot: z.record(z.string(), z.unknown()) }).optional(),
})

function patchData(response: { data: unknown }): Patch {
  return patchSchema.parse(response.data) as Patch
}

export async function submitEvidenceAnswer(
  interviewId: string,
  payload: { question_id: string; question_version: '1.0'; outcome: 'answer' | 'cannot_provide'; answer?: string },
  idempotencyKey = uuid(),
): Promise<{ answer: EvidenceAnswer; interview: EvidenceInterview }> {
  const response = await api<{ data: unknown }>(`/evidence-interviews/${encodeURIComponent(interviewId)}/answers`, {
    method: 'POST',
    headers: { 'Idempotency-Key': idempotencyKey },
    body: payload,
  })
  const data = z.object({ answer: answerSchema, interview: evidenceInterviewSchema }).parse(response.data)
  return { answer: data.answer as EvidenceAnswer, interview: data.interview as EvidenceInterview }
}

export async function generatePatch(interviewId: string, idempotencyKey = uuid()): Promise<Patch> {
  const response = await api<{ data: unknown }>(`/evidence-interviews/${encodeURIComponent(interviewId)}/patches`, {
    method: 'POST',
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return patchData(response)
}

export async function getPatch(patchId: string): Promise<Patch> {
  return patchData(await api<{ data: unknown }>(`/patches/${encodeURIComponent(patchId)}`))
}

export async function editPatch(patchId: string, revision: number, newValue: string, idempotencyKey = uuid()): Promise<Patch> {
  return patchData(await api<{ data: unknown }>(`/patches/${encodeURIComponent(patchId)}`, {
    method: 'PATCH',
    headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': idempotencyKey },
    body: { new_value: newValue },
  }))
}

export async function rejectPatch(patchId: string, revision: number, idempotencyKey = uuid()): Promise<Patch> {
  return patchData(await api<{ data: unknown }>(`/patches/${encodeURIComponent(patchId)}/reject`, {
    method: 'POST',
    headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': idempotencyKey },
    body: { confirmed: true },
  }))
}

export async function approvePatch(patchId: string, revision: number, idempotencyKey = uuid()): Promise<Patch> {
  return patchData(await api<{ data: unknown }>(`/patches/${encodeURIComponent(patchId)}/approve`, {
    method: 'POST',
    headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': idempotencyKey },
    body: { confirmed: true },
  }))
}

export async function regeneratePatch(patchId: string, revision: number, idempotencyKey = uuid()): Promise<Patch> {
  return patchData(await api<{ data: unknown }>(`/patches/${encodeURIComponent(patchId)}/regenerate`, {
    method: 'POST',
    headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': idempotencyKey },
  }))
}
