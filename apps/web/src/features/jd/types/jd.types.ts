export interface JobDescription {
  id: string
  company: string | null
  role: string | null
  current_revision: JobDescriptionRevision | null
  deleted_at: string | null
  created_at: string
  updated_at: string
}

export interface JobDescriptionRevision {
  id: string
  job_description_id: string
  revision_number: number
  raw_text: string
  company: string | null
  role: string | null
  created_at: string
}

export type SignalState = 'detected' | 'absent' | 'unknown'
export interface SignalList<T = string> {
  state: SignalState
  items: T[]
}
export interface SignalValue<T = string> {
  state: SignalState
  value: T | null
}

export interface JdAnalysis {
  id: string
  job_description_revision_id: string
  analysis_schema_version: string
  analysis_rule_version: string
  status: 'succeeded' | 'failed' | 'pending' | 'running'
  signals: {
    role: SignalValue
    required_skills: SignalList<{ signal_id: string; label: string }>
    nice_to_have_skills: SignalList<{ signal_id: string; label: string }>
    responsibilities: SignalList
    keywords: SignalList<{ signal_id: string; label: string }>
    seniority: SignalValue
    soft_skills: SignalList
    domain_context: SignalList
  }
  created_at: string
}
