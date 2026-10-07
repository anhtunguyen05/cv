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
  answers: EvidenceAnswer[]
  progress: EvidenceProgress
}

export interface EvidenceAnswer {
  id: string
  interview_id: string
  question_id: string
  question_version: '1.0'
  area_signal_id: string
  outcome: 'answer' | 'cannot_provide'
  answer: string | null
  provenance: 'user'
  created_at: string
}

export interface EvidenceProgress {
  answered: number
  total: number
  remaining: number
}

export interface PatchTarget {
  section: 'summary' | 'experience' | 'projects'
  field: 'summary' | 'highlights'
  item_id: string | null
  operation: 'replace' | 'append'
  highlight_index?: number
}

export interface Patch {
  id: string
  source_cv_version_id: string
  match_report_id: string
  interview_id: string
  predecessor_patch_id: string | null
  status: 'pending_validation' | 'pending' | 'rejected' | 'invalid' | 'applied'
  allowed_actions: Array<'edit' | 'reject' | 'approve' | 'regenerate'>
  revision: number
  patch_schema_version: string
  prompt_version: string
  provider_model_version: string
  target: PatchTarget
  old_value: string | null | { collection_hash: string }
  new_value: string
  reason: string
  evidence_source_ids: string[]
  provenance: Record<string, unknown>
  applied_version_id: string | null
  created_at: string
  updated_at: string
  source?: { id: string; snapshot: Record<string, unknown> } | null
  evidence?: EvidenceAnswer[]
  result_version?: { id: string; source_cv_version_id: string; snapshot: Record<string, unknown> }
}
