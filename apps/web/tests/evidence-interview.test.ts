import { beforeEach, describe, expect, it, vi } from 'vitest'

const { api } = vi.hoisted(() => ({ api: vi.fn() }))

vi.mock('@/shared/api/client', () => ({ api }))

import { getEvidenceInterview, startEvidenceInterview } from '@/features/evidence/api/evidence.api'

const validInterview = {
  id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  match_report_id: '01ARZ3NDEKTSV4RRFFQ69G5FAW',
  cv_version_id: '01ARZ3NDEKTSV4RRFFQ69G5FAX',
  job_description_id: '01ARZ3NDEKTSV4RRFFQ69G5FAY',
  job_description_revision_id: '01ARZ3NDEKTSV4RRFFQ69G5FAZ',
  analysis_id: '01ARZ3NDEKTSV4RRFFQ69G5FB0',
  areas: [{
    signal_id: 'typescript',
    label: 'TypeScript',
    importance: 'required',
    evidence_level: 'missing',
    source_references: ['requirements[0]'],
  }],
  questions: [{
    id: '01ARZ3NDEKTSV4RRFFQ69G5FB1',
    area_signal_id: 'typescript',
    question_version: '1.0',
    question: 'What specific experience can you share that demonstrates your work with TypeScript?',
  }],
  question_set_version: '1.0',
  status: 'active',
  expires_at: '2026-10-14T00:00:00.000Z',
  created_at: '2026-10-07T00:00:00.000Z',
  updated_at: '2026-10-07T00:00:00.000Z',
}

describe('Evidence Interview transport', () => {
  beforeEach(() => api.mockReset())

  it('starts an interview with only the server-owned report path and idempotency key', async () => {
    api.mockResolvedValue({ data: validInterview })

    await expect(startEvidenceInterview(validInterview.match_report_id, '11111111-1111-4111-8111-111111111111'))
      .resolves.toMatchObject({ id: validInterview.id, areas: validInterview.areas })

    expect(api).toHaveBeenCalledWith(
      `/match-reports/${validInterview.match_report_id}/evidence-interviews`,
      expect.objectContaining({
        method: 'POST',
        headers: { 'Idempotency-Key': '11111111-1111-4111-8111-111111111111' },
      }),
    )
    expect(api.mock.calls[0]?.[1]).not.toHaveProperty('body')
  })

  it('rejects malformed server data before the interview page renders it', async () => {
    api.mockResolvedValue({ data: { ...validInterview, questions: [{ ...validInterview.questions[0], question: '' }] } })

    await expect(getEvidenceInterview(validInterview.id)).rejects.toThrow()
  })

  it('rejects more than five server-selected areas', async () => {
    api.mockResolvedValue({
      data: {
        ...validInterview,
        areas: [...validInterview.areas, ...validInterview.areas, ...validInterview.areas, ...validInterview.areas, ...validInterview.areas, ...validInterview.areas],
      },
    })

    await expect(getEvidenceInterview(validInterview.id)).rejects.toThrow()
  })

  it('rejects a question that is not linked to a stored area', async () => {
    api.mockResolvedValue({
      data: {
        ...validInterview,
        questions: [{ ...validInterview.questions[0], area_signal_id: 'unknown-area' }],
      },
    })

    await expect(getEvidenceInterview(validInterview.id)).rejects.toThrow()
  })

  it('rejects invalid lifecycle timestamps', async () => {
    api.mockResolvedValue({ data: { ...validInterview, expires_at: 'not-a-date' } })

    await expect(getEvidenceInterview(validInterview.id)).rejects.toThrow()
  })
})
