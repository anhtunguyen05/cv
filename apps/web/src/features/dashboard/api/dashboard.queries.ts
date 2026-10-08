import { useQuery } from '@tanstack/vue-query'
import { getDashboardSummary } from './dashboard.api'
import { dashboardQueryKeys } from './dashboard.keys'

export function useDashboardSummaryQuery() {
  return useQuery({
    queryKey: dashboardQueryKeys.summary(),
    queryFn: getDashboardSummary,
    staleTime: 30_000,
  })
}
