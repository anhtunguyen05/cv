export interface PersonalInformation {
  full_name: string
  headline: string | null
  email: string | null
  phone: string | null
  location: string | null
  website_url: string | null
  linkedin_url: string | null
  github_url: string | null
}

export interface SkillItem {
  id?: string
  name: string
}
export interface SkillCategory {
  id?: string
  label: string
  items: SkillItem[]
}
export interface EducationItem {
  id?: string
  institution: string
  degree: string
  field_of_study: string | null
  location: string | null
  start_date: string | null
  end_date: string | null
  description: string | null
}
export interface ExperienceItem {
  id?: string
  organization: string
  role: string
  employment_type: string | null
  location: string | null
  start_date: string
  end_date: string | null
  is_current: boolean
  highlights: string[]
}
export interface ProjectItem {
  id?: string
  name: string
  role: string | null
  url: string | null
  start_date: string | null
  end_date: string | null
  technologies: string[]
  highlights: string[]
}
export interface CertificateItem {
  id?: string
  name: string
  issuer: string
  issued_on: string | null
  expires_on: string | null
  credential_url: string | null
}
export interface LanguageItem {
  id?: string
  language: string
  proficiency: string
}
export interface ActivityItem {
  id?: string
  name: string
  role: string | null
  organization: string | null
  start_date: string | null
  end_date: string | null
  description: string | null
}

export interface CvDocument {
  personal_information: PersonalInformation
  summary: string | null
  skills: SkillCategory[]
  education: EducationItem[]
  experience: ExperienceItem[]
  projects: ProjectItem[]
  certificates: CertificateItem[]
  languages: LanguageItem[]
  activities: ActivityItem[]
}

export interface CvProfile extends CvDocument {
  id: string
  title: string
  revision: number
  created_at: string
  updated_at: string
}

export interface CvVersion {
  id: string
  name: string
  source_profile_id: string
  source_profile_revision: number
  snapshot_schema_version: string
  snapshot: CvDocument & { title: string }
  created_at: string
}

export interface CvPreviewSection {
  key: string
  data: unknown
}

export interface CvPreview {
  cv_version_id: string
  template_id: string
  template_version: string
  template_name: string
  renderer_version: string
  version_name: string
  snapshot_schema_version: string
  source_profile_revision: number
  sections: CvPreviewSection[]
  rendered_at: string | null
}

export type CvSectionKey = keyof CvDocument
