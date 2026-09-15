export interface JobDescription {
  id: number
  user_id: number
  company_name: string
  job_title: string
  raw_text: string
  analysis_json?: JdAnalysis
  created_at: string
  updated_at: string
}

export interface JdAnalysis {
  role: string
  level: string
  required_skills: string[]
  nice_to_have: string[]
  responsibilities: string[]
  keywords: string[]
  soft_skills: string[]
  domain?: string
}
