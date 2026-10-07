import { api } from '@/shared/api/client'
import type {
  CvProfile,
  CvVersion,
  CvSectionKey,
  CvDocument,
  PersonalInformation,
} from '../types/cv.types'

interface Collection<T> {
  data: T[]
  meta: { current_page: number; per_page: number; total: number; last_page: number }
  links: Record<string, string | null>
}
const uuid = () => crypto.randomUUID()

export async function getCvProfiles(): Promise<CvProfile[]> {
  const response = await api<Collection<CvProfile>>('/cv-profiles')
  return response.data
}

export async function getCvProfile(id: string): Promise<CvProfile> {
  const response = await api<{ data: CvProfile }>(`/cv-profiles/${encodeURIComponent(id)}`)
  return response.data
}

export async function createCvProfile(
  title: string,
  personal_information: PersonalInformation,
): Promise<CvProfile> {
  const response = await api<{ data: CvProfile }>('/cv-profiles', {
    method: 'POST',
    body: { title, personal_information },
    headers: { 'Idempotency-Key': uuid() },
  })
  return response.data
}

export async function updateCvSection(
  id: string,
  section: CvSectionKey,
  value: CvDocument[CvSectionKey],
  revision: number,
): Promise<CvProfile> {
  const path = section === 'personal_information' ? 'personal-information' : section
  const response = await api<{ data: CvProfile }>(
    `/cv-profiles/${encodeURIComponent(id)}/${path}`,
    {
      method: 'PUT',
      body: { [section]: value },
      headers: { 'If-Match': `"${revision}"` },
    },
  )
  return response.data
}

export async function updateCvTitle(
  id: string,
  title: string,
  revision: number,
): Promise<CvProfile> {
  const response = await api<{ data: CvProfile }>(`/cv-profiles/${encodeURIComponent(id)}/title`, {
    method: 'PUT',
    body: { title },
    headers: { 'If-Match': `"${revision}"` },
  })
  return response.data
}

export async function getCvVersions(profileId?: string): Promise<CvVersion[]> {
  const query = profileId ? `?profile_id=${encodeURIComponent(profileId)}` : ''
  const response = await api<Collection<CvVersion>>(`/cv-versions${query}`)
  return response.data
}

export interface CvVersionPage {
  items: CvVersion[]
  total: number
  lastPage: number
}

export async function getCvVersionsPage(page = 1, perPage = 20, profileId?: string): Promise<CvVersionPage> {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) })
  if (profileId) params.set('profile_id', profileId)
  const response = await api<Collection<CvVersion>>(`/cv-versions?${params.toString()}`)
  return { items: response.data, total: response.meta.total, lastPage: response.meta.last_page }
}

export async function getCvVersion(id: string): Promise<CvVersion> {
  const response = await api<{ data: CvVersion }>(`/cv-versions/${encodeURIComponent(id)}`)
  return response.data
}

export async function createCvVersion(
  profileId: string,
  name: string,
  revision: number,
): Promise<CvVersion> {
  const response = await api<{ data: CvVersion }>(
    `/cv-profiles/${encodeURIComponent(profileId)}/versions`,
    {
      method: 'POST',
      body: { name },
      headers: { 'If-Match': `"${revision}"`, 'Idempotency-Key': uuid() },
    },
  )
  return response.data
}
