import { z } from 'zod'
import type { CvDocument, CvSectionKey } from '../types/cv.types'

const optionalId = z.string().optional()

export const personalInformationSchema = z.object({
  full_name: z.string(),
  headline: z.string().nullable(),
  email: z.string().nullable(),
  phone: z.string().nullable(),
  location: z.string().nullable(),
  website_url: z.string().nullable(),
  linkedin_url: z.string().nullable(),
  github_url: z.string().nullable(),
})

const skillItemSchema = z.object({ id: optionalId, name: z.string() })
const skillCategorySchema = z.object({
  id: optionalId,
  label: z.string(),
  items: z.array(skillItemSchema),
})
const educationItemSchema = z.object({
  id: optionalId,
  institution: z.string(),
  degree: z.string(),
  field_of_study: z.string().nullable(),
  location: z.string().nullable(),
  start_date: z.string().nullable(),
  end_date: z.string().nullable(),
  description: z.string().nullable(),
})
const experienceItemSchema = z.object({
  id: optionalId,
  organization: z.string(),
  role: z.string(),
  employment_type: z.string().nullable(),
  location: z.string().nullable(),
  start_date: z.string(),
  end_date: z.string().nullable(),
  is_current: z.boolean(),
  highlights: z.array(z.string()),
})
const projectItemSchema = z.object({
  id: optionalId,
  name: z.string(),
  role: z.string().nullable(),
  url: z.string().nullable(),
  start_date: z.string().nullable(),
  end_date: z.string().nullable(),
  technologies: z.array(z.string()),
  highlights: z.array(z.string()),
})
const certificateItemSchema = z.object({
  id: optionalId,
  name: z.string(),
  issuer: z.string(),
  issued_on: z.string().nullable(),
  expires_on: z.string().nullable(),
  credential_url: z.string().nullable(),
})
const languageItemSchema = z.object({
  id: optionalId,
  language: z.string(),
  proficiency: z.string(),
})
const activityItemSchema = z.object({
  id: optionalId,
  name: z.string(),
  role: z.string().nullable(),
  organization: z.string().nullable(),
  start_date: z.string().nullable(),
  end_date: z.string().nullable(),
  description: z.string().nullable(),
})

const cvDocumentObjectSchema = z.object({
  personal_information: personalInformationSchema,
  summary: z.string().nullable(),
  skills: z.array(skillCategorySchema),
  education: z.array(educationItemSchema),
  experience: z.array(experienceItemSchema),
  projects: z.array(projectItemSchema),
  certificates: z.array(certificateItemSchema),
  languages: z.array(languageItemSchema),
  activities: z.array(activityItemSchema),
})

export const cvDocumentSchema: z.ZodType<CvDocument> = cvDocumentObjectSchema

export const cvProfileSchema = cvDocumentObjectSchema.extend({
  id: z.string().min(1),
  title: z.string(),
  revision: z.number().int().nonnegative(),
  created_at: z.string().min(1),
  updated_at: z.string().min(1),
})

export const cvVersionSchema = z.object({
  id: z.string().min(1),
  name: z.string(),
  source_profile_id: z.string().min(1),
  source_profile_revision: z.number().int().nonnegative(),
  snapshot_schema_version: z.string().min(1),
  snapshot: cvDocumentObjectSchema.extend({ title: z.string() }),
  created_at: z.string().min(1),
})

const previewDataSchema = z.union([
  z.string(),
  z.number(),
  z.boolean(),
  z.null(),
  z.record(z.string(), z.unknown()),
  z.array(z.unknown()),
])

export const cvPreviewSchema = z.object({
  cv_version_id: z.string().min(1),
  template_id: z.string().min(1),
  template_version: z.string().min(1),
  template_name: z.string(),
  renderer_version: z.string(),
  version_name: z.string(),
  snapshot_schema_version: z.string().min(1),
  source_profile_revision: z.number().int().nonnegative(),
  sections: z.array(z.object({ key: z.string(), data: previewDataSchema })),
  rendered_at: z.string().nullable(),
})

export const cvSectionSchemas: {
  [K in CvSectionKey]: z.ZodType<CvDocument[K]>
} = {
  personal_information: personalInformationSchema,
  summary: z.string().nullable(),
  skills: z.array(skillCategorySchema),
  education: z.array(educationItemSchema),
  experience: z.array(experienceItemSchema),
  projects: z.array(projectItemSchema),
  certificates: z.array(certificateItemSchema),
  languages: z.array(languageItemSchema),
  activities: z.array(activityItemSchema),
}
