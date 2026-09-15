import { api } from '@/shared/api/client'
import type { JobDescription } from '../types/jd.types'

export function getJobDescriptions(): Promise<JobDescription[]> {
  return api<JobDescription[]>('/job-descriptions')
}

export function createJobDescription(data: { raw_text: string; company_name?: string; job_title?: string }): Promise<JobDescription> {
  return api<JobDescription>('/job-descriptions', { method: 'POST', body: data })
}

export function analyzeJobDescription(id: number): Promise<JobDescription> {
  return api<JobDescription>(`/job-descriptions/${id}/analyze`, { method: 'POST' })
}
