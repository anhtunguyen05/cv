export interface SkillMatch {
  skill: string
  status: 'matched' | 'missing' | 'weak'
  evidence?: string
}

export interface SectionRecommendation {
  section: string
  recommendation: string
  priority: 'high' | 'medium' | 'low'
}

export interface MatchReport {
  id: number
  cv_version_id: number
  job_description_id: number
  score: number
  matched_skills_json: SkillMatch[]
  missing_skills_json: SkillMatch[]
  weak_sections_json: SkillMatch[]
  recommendations_json: SectionRecommendation[]
  created_at: string
  updated_at: string
}
