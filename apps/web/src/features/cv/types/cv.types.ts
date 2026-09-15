export interface PersonalInfo {
  full_name: string
  email: string
  phone: string
  location: string
  github?: string
  linkedin?: string
  portfolio?: string
}

export interface CvSkills {
  frontend?: string[]
  backend?: string[]
  tools?: string[]
  [key: string]: string[] | undefined
}

export interface CvProject {
  id: string
  name: string
  role: string
  tech_stack: string[]
  bullets: string[]
  period?: string
  url?: string
}

export interface CvEducation {
  school: string
  major: string
  period: string
  gpa?: string
}

export interface CvCertificate {
  name: string
  issuer: string
  date: string
  url?: string
}

export interface CvData {
  personal_info: PersonalInfo
  summary: string
  skills: CvSkills
  projects: CvProject[]
  education: CvEducation[]
  certificates?: CvCertificate[]
  languages?: Array<{ name: string; level: string }>
  activities?: string[]
}

export interface CvProfile {
  id: number
  user_id: number
  title: string
  base_data_json: CvData
  created_at: string
  updated_at: string
}

export interface CvVersion {
  id: number
  cv_profile_id: number
  job_description_id?: number
  template_id: number
  version_name: string
  data_json: CvData
  match_score?: number
  created_at: string
  updated_at: string
}

export type CvSectionKey =
  | 'personal_info'
  | 'summary'
  | 'skills'
  | 'projects'
  | 'education'
  | 'certificates'
  | 'languages'
  | 'activities'
