import { api } from '@/shared/api/client'
import type { MatchReport } from '../types/match.types'

export function createMatchReport(
  cvVersionId: number,
  jobDescriptionId: number,
): Promise<MatchReport> {
  return api<MatchReport>('/match-reports', {
    method: 'POST',
    body: { cv_version_id: cvVersionId, job_description_id: jobDescriptionId },
  })
}

export function getMatchReport(id: number): Promise<MatchReport> {
  return api<MatchReport>(`/match-reports/${id}`)
}
