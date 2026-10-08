import { api } from '@/shared/api/client'
import { dashboardSummarySchema } from './dashboard.schemas'
import type { DashboardSummary } from './dashboard.schemas'

export async function getDashboardSummary(): Promise<DashboardSummary> {
  const response = await api<{ data: unknown }>('/dashboard/summary')
  return dashboardSummarySchema.parse(response.data)
}
