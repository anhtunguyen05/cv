import { z } from 'zod'

export const dashboardSummarySchema = z.object({
  cv_profiles: z.number().int().nonnegative(),
  job_descriptions: z.number().int().nonnegative(),
  match_reports: z.number().int().nonnegative(),
})

export type DashboardSummary = z.infer<typeof dashboardSummarySchema>
