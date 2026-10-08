import { api } from '@/shared/api/client'
import { paginatedResponseSchema } from '@/shared/schemas/pagination.schemas'
import { cvPreviewSchema, cvProfileSchema, cvVersionSchema } from '../schemas/cv.schemas'
import type { PageResult } from '@/shared/types/api.types'
import type {
  CvProfile,
  CvVersion,
  CvSectionKey,
  CvDocument,
  PersonalInformation,
  CvPreview,
} from '../types/cv.types'

const uuid = () => crypto.randomUUID()

export async function getCvProfiles(): Promise<PageResult<CvProfile>> {
  const response = paginatedResponseSchema.parse(await api<unknown>('/cv-profiles'))
  return {
    items: response.data.map((item) => cvProfileSchema.parse(item)),
    page: response.meta.page ?? response.meta.current_page ?? 1,
    perPage: response.meta.per_page,
    total: response.meta.total,
    lastPage:
      response.meta.last_page ??
      Math.max(1, Math.ceil(response.meta.total / response.meta.per_page)),
  }
}

export async function getCvProfile(id: string): Promise<CvProfile> {
  const response = await api<{ data: unknown }>(`/cv-profiles/${encodeURIComponent(id)}`)
  return cvProfileSchema.parse(response.data)
}

export async function createCvProfile(
  title: string,
  personal_information: PersonalInformation,
): Promise<CvProfile> {
  const response = await api<{ data: unknown }>('/cv-profiles', {
    method: 'POST',
    body: { title, personal_information },
    headers: { 'Idempotency-Key': uuid() },
  })
  return cvProfileSchema.parse(response.data)
}

export async function updateCvSection(
  id: string,
  section: CvSectionKey,
  value: CvDocument[CvSectionKey],
  revision: number,
): Promise<CvProfile> {
  const path = section === 'personal_information' ? 'personal-information' : section
  const response = await api<{ data: unknown }>(`/cv-profiles/${encodeURIComponent(id)}/${path}`, {
    method: 'PUT',
    body: { [section]: value },
    headers: { 'If-Match': `"${revision}"` },
  })
  return cvProfileSchema.parse(response.data)
}

export async function updateCvTitle(
  id: string,
  title: string,
  revision: number,
): Promise<CvProfile> {
  const response = await api<{ data: unknown }>(`/cv-profiles/${encodeURIComponent(id)}/title`, {
    method: 'PUT',
    body: { title },
    headers: { 'If-Match': `"${revision}"` },
  })
  return cvProfileSchema.parse(response.data)
}

export async function getCvVersions(profileId?: string): Promise<CvVersion[]> {
  const query = profileId ? `?profile_id=${encodeURIComponent(profileId)}` : ''
  const response = paginatedResponseSchema.parse(await api<unknown>(`/cv-versions${query}`))
  return response.data.map((item) => cvVersionSchema.parse(item))
}

export async function getCvVersionsPage(
  page = 1,
  perPage = 20,
  profileId?: string,
): Promise<PageResult<CvVersion>> {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) })
  if (profileId) params.set('profile_id', profileId)
  const response = paginatedResponseSchema.parse(
    await api<unknown>(`/cv-versions?${params.toString()}`),
  )
  return {
    items: response.data.map((item) => cvVersionSchema.parse(item)),
    page: response.meta.page ?? response.meta.current_page ?? page,
    perPage: response.meta.per_page,
    total: response.meta.total,
    lastPage:
      response.meta.last_page ??
      Math.max(1, Math.ceil(response.meta.total / response.meta.per_page)),
  }
}

export async function getCvVersion(id: string): Promise<CvVersion> {
  const response = await api<{ data: unknown }>(`/cv-versions/${encodeURIComponent(id)}`)
  return cvVersionSchema.parse(response.data)
}

export async function getCvPreview(
  versionId: string,
  templateId: string,
  templateVersion: string,
): Promise<CvPreview> {
  const params = new URLSearchParams({ template_id: templateId, template_version: templateVersion })
  const response = await api<{ data: unknown }>(
    `/cv-versions/${encodeURIComponent(versionId)}/preview?${params.toString()}`,
  )
  return cvPreviewSchema.parse(response.data)
}

export async function createCvVersion(
  profileId: string,
  name: string,
  revision: number,
): Promise<CvVersion> {
  const response = await api<{ data: unknown }>(
    `/cv-profiles/${encodeURIComponent(profileId)}/versions`,
    {
      method: 'POST',
      body: { name },
      headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': uuid() },
    },
  )
  return cvVersionSchema.parse(response.data)
}
