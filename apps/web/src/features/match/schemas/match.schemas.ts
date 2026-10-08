import { z } from 'zod'

const ulid = z.string().regex(/^[0-9A-HJKMNP-TV-Z]{26}$/)

export const skillMatchSchema = z
  .object({
    signal_id: z.string(),
    label: z.string(),
    importance: z.enum(['required', 'preferred']),
    evidence_level: z.enum(['strong', 'weak', 'missing']),
    source_references: z.array(z.string()),
  })
  .passthrough()

export const sectionRecommendationSchema = z
  .object({
    id: z.string(),
    target_cv_section: z.string(),
    related_signal_ids: z.array(z.string()),
    rationale: z.string(),
    action: z.string(),
    priority: z.enum(['high', 'medium', 'low']),
  })
  .passthrough()

export const matchReportSchema = z
  .object({
    id: ulid,
    cv_version_id: ulid,
    job_description_id: ulid,
    job_description_revision_id: ulid,
    analysis_id: ulid,
    analysis_rule_version: z.string(),
    matching_rule_version: z.string(),
    report_schema_version: z.string(),
    overall_score: z.number().finite().min(0).max(100),
    matched_skills: z.array(skillMatchSchema),
    missing_skills: z.array(skillMatchSchema),
    weak_evidence: z.array(skillMatchSchema),
    recommendations: z.array(sectionRecommendationSchema),
    source_summary: z.object({
      company: z.string().nullable(),
      role: z.string().nullable(),
      revision_number: z.number().nullable(),
      source_deleted: z.boolean(),
      source_is_current: z.boolean(),
    }),
    created_at: z.string().nullable(),
  })
  .passthrough()

export type SkillMatch = z.infer<typeof skillMatchSchema>
export type SectionRecommendation = z.infer<typeof sectionRecommendationSchema>
export type MatchReport = z.infer<typeof matchReportSchema>
