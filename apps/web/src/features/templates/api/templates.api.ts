import { api } from '@/shared/api/client'
import type { CvTemplate } from '../types/template.types'

export function getTemplates(): Promise<CvTemplate[]> {
  return api<CvTemplate[]>('/templates')
}

export function getTemplate(id: number): Promise<CvTemplate> {
  return api<CvTemplate>(`/templates/${id}`)
}
