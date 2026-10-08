import { computed, ref, watch } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { useRoute, useRouter } from 'vue-router'
import { startEvidenceInterview } from '@/features/evidence/api/evidence.api'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'
import { useMatchReportQuery } from '../api/match.queries'
import type { SectionRecommendation } from '../types/match.types'

export const matchPriorityLabels: Record<SectionRecommendation['priority'], string> = {
  high: 'High priority',
  medium: 'Medium priority',
  low: 'Optional',
}

export function useMatchReportController() {
  const route = useRoute()
  const router = useRouter()
  const matchId = computed(() => String(route.params.matchId || ''))
  const reportQuery = useMatchReportQuery(matchId)
  const routeGeneration = ref(0)
  const retryable = computed(() => {
    const error = reportQuery.error.value
    return error instanceof ApiRequestError ? error.status === 429 || error.status >= 500 : true
  })
  const unresolvedAreaCount = computed(() => {
    const report = reportQuery.data.value
    return report ? report.missing_skills.length + report.weak_evidence.length : 0
  })
  const interviewAreaCount = computed(() => Math.min(unresolvedAreaCount.value, 5))
  const interviewMutation = useMutation({
    mutationFn: (input: { matchId: string; generation: number }) =>
      startEvidenceInterview(input.matchId),
    onSuccess: (interview, input) => {
      if (
        input.generation === routeGeneration.value &&
        input.matchId === matchId.value &&
        interview.match_report_id === input.matchId
      ) {
        void router.push(ROUTES.AI_INTERVIEW(interview.id))
      }
    },
  })
  const interviewError = computed(() => interviewMutation.error.value)
  const interviewNotNeeded = computed(
    () =>
      interviewError.value instanceof ApiRequestError &&
      interviewError.value.code === 'INTERVIEW_NOT_NEEDED',
  )
  const interviewConflict = computed(
    () =>
      interviewError.value instanceof ApiRequestError &&
      interviewError.value.code === 'EVIDENCE_SESSION_CONFLICT',
  )
  const conflictInterviewId = computed(() => {
    const details =
      interviewError.value instanceof ApiRequestError ? interviewError.value.details : undefined
    return details && typeof details.interview_id === 'string' ? details.interview_id : undefined
  })

  function startInterview(): void {
    if (unresolvedAreaCount.value === 0 || interviewMutation.isPending.value) return
    interviewMutation.mutate({ matchId: matchId.value, generation: routeGeneration.value })
  }

  function refreshReport(): void {
    interviewMutation.reset()
    void reportQuery.refetch()
  }

  function recoverInterviewConflict(): void {
    if (conflictInterviewId.value) {
      void router.push(ROUTES.AI_INTERVIEW(conflictInterviewId.value))
    } else refreshReport()
  }

  watch(matchId, () => {
    routeGeneration.value += 1
    interviewMutation.reset()
  })

  return {
    reportQuery,
    retryable,
    unresolvedAreaCount,
    interviewAreaCount,
    interviewMutation,
    interviewError,
    interviewNotNeeded,
    interviewConflict,
    conflictInterviewId,
    startInterview,
    refreshReport,
    recoverInterviewConflict,
  }
}
