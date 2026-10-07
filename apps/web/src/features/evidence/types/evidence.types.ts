export interface EvidenceArea {
  signal_id: string
  label: string
  importance: 'required' | 'preferred'
  evidence_level: 'missing' | 'weak'
  source_references: string[]
}

export interface EvidenceQuestion {
  id: string
  area_signal_id: string
  question_version: '1.0'
  question: string
}

export interface EvidenceInterview {
  id: string
  match_report_id: string
  cv_version_id: string
  job_description_id: string
  job_description_revision_id: string
  analysis_id: string
  areas: EvidenceArea[]
  questions: EvidenceQuestion[]
  question_set_version: '1.0'
  status: 'active' | 'completed' | 'expired'
  expires_at: string
  created_at: string
  updated_at: string
}
