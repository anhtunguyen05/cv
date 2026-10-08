import { useQuery } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { getTemplates } from './templates.api'
import { templateQueryKeys } from './templates.keys'

export function useTemplatesQuery(enabled: MaybeRefOrGetter<boolean> = true) {
  return useQuery({
    queryKey: templateQueryKeys.list(),
    queryFn: getTemplates,
    enabled: computed(() => toValue(enabled)),
    staleTime: 10 * 60 * 1000,
    gcTime: 30 * 60 * 1000,
  })
}
