import { api } from '@/shared/api/client'
import type { CvTemplate } from '../types/template.types'

export async function getTemplates(): Promise<CvTemplate[]> {
  const response = await api<{ data: CvTemplate[] }>('/templates')
  return response.data
}
