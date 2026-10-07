import { beforeEach, describe, expect, it, vi } from 'vitest'

const { api } = vi.hoisted(() => ({ api: vi.fn() }))

vi.mock('@/shared/api/client', () => ({ api }))

import { getMatchReport } from '@/features/match/api/match.api'

const validReport = {
  id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  cv_version_id: '01ARZ3NDEKTSV4RRFFQ69G5FAW',
  job_description_id: '01ARZ3NDEKTSV4RRFFQ69G5FAX',
  job_description_revision_id: '01ARZ3NDEKTSV4RRFFQ69G5FAY',
  analysis_id: '01ARZ3NDEKTSV4RRFFQ69G5FAZ',
  analysis_rule_version: '1.0.0',
  matching_rule_version: '1.0.0',
  report_schema_version: '1.0.0',
  overall_score: 73.53,
  matched_skills: [],
  missing_skills: [],
  weak_evidence: [],
  recommendations: [],
  source_summary: { company: 'Example Co', role: 'Engineer', revision_number: 1, source_deleted: false, source_is_current: true },
  created_at: '2026-10-07T00:00:00.000Z',
}

describe('match report detail transport', () => {
  beforeEach(() => api.mockReset())

  it('parses the stored detail contract', async () => {
    api.mockResolvedValue({ data: validReport })

    await expect(getMatchReport(validReport.id)).resolves.toMatchObject({
      id: validReport.id,
      overall_score: 73.53,
    })
  })

  it('rejects malformed detail responses before rendering', async () => {
    api.mockResolvedValue({ data: { ...validReport, overall_score: '73.53' } })

    await expect(getMatchReport(validReport.id)).rejects.toThrow()
  })
})
