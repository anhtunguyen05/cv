import { useQuery } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import { ApiRequestError } from '@/shared/api/client'
import { getMatchReport, getMatchReports } from './match.api'
import { matchQueryKeys } from './match.keys'

export function useMatchReportsQuery(page: MaybeRef<number> = 1, perPage = 20) {
  return useQuery({
    queryKey: computed(() => matchQueryKeys.list(toValue(page), perPage)),
    queryFn: () => getMatchReports(toValue(page), perPage),
    retry: (count, error) =>
      error instanceof ApiRequestError && [429, 503].includes(error.status) && count < 2,
  })
}

export function useMatchReportQuery(id: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => matchQueryKeys.detail(toValue(id))),
    queryFn: () => getMatchReport(toValue(id)),
    enabled: computed(() => Boolean(toValue(id))),
    retry: (count, error) => {
      const retryable =
        !(error instanceof ApiRequestError) || error.status === 429 || error.status >= 500
      return retryable && count < 2
    },
  })
}
