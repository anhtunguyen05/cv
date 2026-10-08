import { api } from '@/shared/api/client'
import type { CvTemplate } from '../types/template.types'
import { cvTemplateSchema } from '../schemas/template.schemas'

export async function getTemplates(): Promise<CvTemplate[]> {
  const response = await api<{ data: unknown }>('/templates')
  return cvTemplateSchema.array().parse(response.data)
}
