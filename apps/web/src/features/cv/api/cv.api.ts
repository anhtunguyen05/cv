import { api } from '@/shared/api/client'
import type { CvProfile, CvVersion } from '../types/cv.types'

// CV Profiles
export function getCvProfiles(): Promise<CvProfile[]> {
  return api<CvProfile[]>('/cv-profiles')
}

export function getCvProfile(id: number): Promise<CvProfile> {
  return api<CvProfile>(`/cv-profiles/${id}`)
}

export function createCvProfile(data: Partial<CvProfile>): Promise<CvProfile> {
  return api<CvProfile>('/cv-profiles', { method: 'POST', body: data })
}

export function updateCvProfile(id: number, data: Partial<CvProfile>): Promise<CvProfile> {
  return api<CvProfile>(`/cv-profiles/${id}`, { method: 'PUT', body: data })
}

export function deleteCvProfile(id: number): Promise<void> {
  return api<void>(`/cv-profiles/${id}`, { method: 'DELETE' })
}

// CV Versions
export function getCvVersions(profileId: number): Promise<CvVersion[]> {
  return api<CvVersion[]>(`/cv-profiles/${profileId}/versions`)
}

export function getCvVersion(id: number): Promise<CvVersion> {
  return api<CvVersion>(`/cv-versions/${id}`)
}

export function createCvVersion(profileId: number, data: Partial<CvVersion>): Promise<CvVersion> {
  return api<CvVersion>(`/cv-profiles/${profileId}/versions`, { method: 'POST', body: data })
}

export function updateCvVersion(id: number, data: Partial<CvVersion>): Promise<CvVersion> {
  return api<CvVersion>(`/cv-versions/${id}`, { method: 'PUT', body: data })
}
