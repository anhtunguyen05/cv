import { computed, toValue, type MaybeRef } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { ApiRequestError } from '@/shared/api/client'
import { getEvidenceInterview, getPatch } from './evidence.api'
import { evidenceQueryKeys } from './evidence.keys'

function retryEvidenceRequest(count: number, error: unknown): boolean {
  const retryable =
    !(error instanceof ApiRequestError) || error.status === 429 || error.status >= 500
  return retryable && count < 2
}

export function useEvidenceInterviewQuery(interviewId: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => evidenceQueryKeys.interview(toValue(interviewId))),
    queryFn: () => getEvidenceInterview(toValue(interviewId)),
    enabled: computed(() => Boolean(toValue(interviewId))),
    retry: retryEvidenceRequest,
    staleTime: 5_000,
  })
}

export function usePatchQuery(patchId: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => evidenceQueryKeys.patch(toValue(patchId))),
    queryFn: () => getPatch(toValue(patchId)),
    enabled: computed(() => Boolean(toValue(patchId))),
    retry: retryEvidenceRequest,
    staleTime: 30_000,
  })
}
