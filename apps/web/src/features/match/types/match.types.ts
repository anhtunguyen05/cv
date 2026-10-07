export interface SkillMatch {
  signal_id: string
  label: string
  importance: 'required' | 'preferred'
  evidence_level: 'strong' | 'weak' | 'missing'
  source_references: string[]
}

export interface SectionRecommendation {
  id: string
  target_cv_section: string
  related_signal_ids: string[]
  rationale: string
  action: string
  priority: 'high' | 'medium' | 'low'
}

export interface MatchReport {
  id: string
  cv_version_id: string
  job_description_id: string
  job_description_revision_id: string
  analysis_id: string
  analysis_rule_version: string
  matching_rule_version: string
  report_schema_version: string
  overall_score: number
  matched_skills: SkillMatch[]
  missing_skills: SkillMatch[]
  weak_evidence: SkillMatch[]
  recommendations: SectionRecommendation[]
  source_summary: { company: string | null; role: string | null; revision_number: number | null; source_deleted: boolean; source_is_current: boolean }
  created_at: string
}
