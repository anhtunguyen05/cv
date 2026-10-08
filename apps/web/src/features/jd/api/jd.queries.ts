import { useQuery } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import { getJobDescription, getJobDescriptionAnalysis, getJobDescriptions } from './jd.api'
import { jdQueryKeys } from './jd.keys'

export function useJobDescriptionsQuery(page: MaybeRef<number>, perPage = 20) {
  return useQuery({
    queryKey: computed(() => jdQueryKeys.list(toValue(page), perPage)),
    queryFn: () => getJobDescriptions(toValue(page), perPage),
  })
}

export function useJobDescriptionQuery(id: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => jdQueryKeys.detail(toValue(id))),
    queryFn: () => getJobDescription(toValue(id)),
    enabled: computed(() => Boolean(toValue(id))),
    retry: false,
  })
}

export function useJobDescriptionAnalysisQuery(id: MaybeRef<string>, analysisId: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => jdQueryKeys.analysis(toValue(id), toValue(analysisId))),
    queryFn: () => getJobDescriptionAnalysis(toValue(id), toValue(analysisId)),
    enabled: computed(() => Boolean(toValue(id)) && Boolean(toValue(analysisId))),
    retry: false,
  })
}
