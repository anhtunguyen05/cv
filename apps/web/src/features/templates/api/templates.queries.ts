import { useQuery } from '@tanstack/vue-query'
import { getTemplates } from './templates.api'
import { templateQueryKeys } from './templates.keys'

export function useTemplatesQuery() {
  return useQuery({
    queryKey: templateQueryKeys.list(),
    queryFn: getTemplates,
  })
}
