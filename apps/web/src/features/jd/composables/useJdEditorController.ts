import { computed, nextTick, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ApiRequestError } from '@/shared/api/client'
import { useCvVersionsPageQuery } from '@/features/cv/api/cv.queries'
import { matchQueryKeys } from '@/features/match/api/match.keys'
import { dashboardQueryKeys } from '@/features/dashboard/api/dashboard.keys'
import { ROUTES } from '@/shared/constants/routes'
import { jdQueryKeys } from '../api/jd.keys'
import { useJobDescriptionAnalysisQuery, useJobDescriptionQuery } from '../api/jd.queries'
import { useJdDraft } from './useJdDraft'
import { useJdWorkflowMutations } from './useJdWorkflowMutations'
import { countCodePoints, limitCodePoints } from '../utils/textLimits'

const ANALYSIS_RULE_VERSION = '1.0.0'
const analysisStorageKey = (revisionId: string, ruleVersion = ANALYSIS_RULE_VERSION) =>
  `careerfitcv:analysis:${revisionId}:${ruleVersion}`

export function useJdEditorController() {
  const route = useRoute()
  const router = useRouter()
  const jobDescriptionId = computed(() => {
    const value = route.params.id
    return typeof value === 'string' && value !== 'new' ? value : ''
  })
  const jdDraft = useJdDraft()
  const { saved, rawText, company, role, hasUnsavedChanges } = jdDraft
  const localError = ref('')
  const errorSummary = ref<HTMLElement | null>(null)
  const storedAnalysisId = ref('')
  const selectedCvVersionId = ref('')
  const cvVersionPage = ref(1)
  const routeGeneration = ref(0)
  const createdRouteId = ref('')
  let pendingSaveKey: string | null = null
  let pendingAnalysisKey: string | null = null
  let pendingMatchKey: string | null = null
  let pendingDeleteKey: string | null = null

  const jdQuery = useJobDescriptionQuery(jobDescriptionId)
  const current = computed(() => {
    const queryData = jdQuery.data.value
    return saved.value ?? (queryData?.id === jobDescriptionId.value ? queryData : null)
  })

  const { queryClient, analysisMutation, saveMutation, matchMutation, deleteMutation } =
    useJdWorkflowMutations({
      onAnalysisSuccess: (result, request) => {
        if (
          request.generation !== routeGeneration.value ||
          request.revisionId !== result.job_description_revision_id ||
          request.jobDescriptionId !== jobDescriptionId.value ||
          current.value?.current_revision?.id !== result.job_description_revision_id
        )
          return
        storedAnalysisId.value = result.id
        if (typeof window !== 'undefined' && window.localStorage) {
          window.localStorage.setItem(
            analysisStorageKey(result.job_description_revision_id, result.analysis_rule_version),
            result.id,
          )
        }
        if (pendingAnalysisKey === request.idempotencyKey) pendingAnalysisKey = null
      },
      onSaveSuccess: async (jd, request) => {
        queryClient.setQueryData(jdQueryKeys.detail(jd.id), jd)
        if (
          request.generation !== routeGeneration.value ||
          request.jobDescriptionId !== jobDescriptionId.value ||
          (request.jobDescriptionId && jd.id !== request.jobDescriptionId)
        )
          return
        const previousRevisionId = request.revisionId
        analysisMutation.reset()
        storedAnalysisId.value = ''
        if (jobDescriptionId.value) {
          queryClient.removeQueries({ queryKey: jdQueryKeys.analysisRoot(jobDescriptionId.value) })
        }
        if (typeof window !== 'undefined' && window.localStorage) {
          if (previousRevisionId)
            window.localStorage.removeItem(analysisStorageKey(previousRevisionId))
          if (jd.current_revision)
            window.localStorage.removeItem(analysisStorageKey(jd.current_revision.id))
        }
        await Promise.all([
          queryClient.invalidateQueries({ queryKey: jdQueryKeys.listRoot() }),
          queryClient.invalidateQueries({ queryKey: dashboardQueryKeys.summary() }),
        ])
        if (request.generation !== routeGeneration.value) return
        if (!jobDescriptionId.value) {
          createdRouteId.value = jd.id
          await router.replace(ROUTES.JD_DETAIL(jd.id))
          if (jobDescriptionId.value !== jd.id) return
        } else if (request.generation !== routeGeneration.value) return
        const localAtResponse = jdDraft.values()
        jdDraft.applyMutationResult(jd, request.submitted, localAtResponse)
        if (pendingSaveKey === request.idempotencyKey) pendingSaveKey = null
      },
      onMatchSuccess: async (report, request) => {
        if (
          request.generation !== routeGeneration.value ||
          request.jobDescriptionId !== jobDescriptionId.value ||
          report.job_description_id !== request.jobDescriptionId
        )
          return
        await Promise.all([
          queryClient.invalidateQueries({ queryKey: matchQueryKeys.all }),
          queryClient.invalidateQueries({ queryKey: dashboardQueryKeys.summary() }),
        ])
        if (
          request.generation !== routeGeneration.value ||
          request.jobDescriptionId !== jobDescriptionId.value
        )
          return
        await router.push(ROUTES.MATCH_REPORT(report.id))
        if (pendingMatchKey === request.idempotencyKey) pendingMatchKey = null
      },
      onDeleteSuccess: async (_result, request) => {
        const deletedId = request.jobDescriptionId
        if (
          request.generation !== routeGeneration.value ||
          !deletedId ||
          deletedId !== jobDescriptionId.value ||
          current.value?.id !== deletedId
        )
          return
        if (pendingDeleteKey === request.idempotencyKey) pendingDeleteKey = null
        queryClient.removeQueries({ queryKey: jdQueryKeys.detail(deletedId) })
        await Promise.all([
          queryClient.invalidateQueries({ queryKey: jdQueryKeys.listRoot() }),
          queryClient.invalidateQueries({ queryKey: dashboardQueryKeys.summary() }),
        ])
        if (request.generation !== routeGeneration.value || deletedId !== jobDescriptionId.value)
          return
        jdDraft.reset()
        await router.push(ROUTES.DASHBOARD)
      },
    })

  const analysisQuery = useJobDescriptionAnalysisQuery(jobDescriptionId, storedAnalysisId)
  const analysis = computed(() => {
    const candidate = !storedAnalysisId.value
      ? analysisMutation.data.value
      : (analysisMutation.data.value ?? analysisQuery.data.value)
    return candidate?.job_description_revision_id === current.value?.current_revision?.id
      ? candidate
      : undefined
  })
  const versionsQuery = useCvVersionsPageQuery(
    cvVersionPage,
    undefined,
    computed(() => Boolean(current.value && analysis.value)),
  )
  const charCount = computed(() => countCodePoints(rawText.value))
  const overLimit = computed(() => charCount.value > 50000)

  function fieldError(field: 'raw_text' | 'company' | 'role'): string {
    const error = saveMutation.error.value
    if (!(error instanceof ApiRequestError)) return ''
    const value = error.details?.[field]
    if (typeof value === 'string') return value
    if (!Array.isArray(value) || value.length === 0) return ''
    const first = value[0]
    return typeof first === 'string'
      ? first
      : typeof first === 'object' &&
          first !== null &&
          'message' in first &&
          typeof first.message === 'string'
        ? first.message
        : ''
  }

  function firstValidationField(): string | null {
    for (const field of ['raw_text', 'company', 'role'] as const) {
      if (fieldError(field)) return field === 'raw_text' ? 'jd-text' : field
    }
    return null
  }

  const errorMessage = computed(() => {
    const error =
      saveMutation.error.value ??
      analysisMutation.error.value ??
      analysisQuery.error.value ??
      matchMutation.error.value ??
      deleteMutation.error.value ??
      jdQuery.error.value
    return error instanceof Error ? error.message : ''
  })
  const retryable = computed(() => {
    const error =
      jdQuery.error.value ??
      saveMutation.error.value ??
      analysisMutation.error.value ??
      analysisQuery.error.value ??
      matchMutation.error.value ??
      deleteMutation.error.value
    return error instanceof ApiRequestError && [429, 503].includes(error.status)
  })
  const saveRetryable = computed(
    () =>
      saveMutation.error.value instanceof ApiRequestError &&
      [429, 503].includes(saveMutation.error.value.status),
  )
  const deleteRetryable = computed(
    () =>
      deleteMutation.error.value instanceof ApiRequestError &&
      [429, 503].includes(deleteMutation.error.value.status),
  )
  const saveConflict = computed(
    () =>
      saveMutation.error.value instanceof ApiRequestError &&
      saveMutation.error.value.status === 409,
  )
  const deleteConflict = computed(
    () =>
      deleteMutation.error.value instanceof ApiRequestError &&
      deleteMutation.error.value.status === 409,
  )
  const analysisConflict = computed(
    () =>
      analysisMutation.error.value instanceof ApiRequestError &&
      analysisMutation.error.value.status === 409,
  )
  const matchRetryable = computed(
    () =>
      matchMutation.error.value instanceof ApiRequestError &&
      [409, 429, 503].includes(matchMutation.error.value.status),
  )

  function submit(): void {
    localError.value = ''
    if (!rawText.value.trim()) {
      localError.value = 'Paste a Job Description before saving.'
      return
    }
    if (overLimit.value) {
      localError.value =
        'The Job Description must be at most 50,000 Unicode code points and 200 KiB.'
      return
    }
    const submitted = jdDraft.values()
    saveMutation.mutate({
      generation: routeGeneration.value,
      jobDescriptionId: jobDescriptionId.value,
      revisionId: current.value?.current_revision?.id ?? '',
      data: {
        raw_text: submitted.rawText,
        company: submitted.company || null,
        role: submitted.role || null,
      },
      submitted,
      idempotencyKey: (pendingSaveKey ??= crypto.randomUUID()),
    })
  }

  const setCompany = (value: string) => {
    company.value = limitCodePoints(value, 160)
  }
  const setRole = (value: string) => {
    role.value = limitCodePoints(value, 160)
  }
  const setRawText = (value: string) => {
    rawText.value = limitCodePoints(value, 50000)
  }
  const setSelectedCvVersionId = (value: string) => {
    selectedCvVersionId.value = value
  }
  const setErrorSummary = (element: HTMLElement | null) => {
    errorSummary.value = element
  }
  const previousCvVersionPage = () => {
    if (cvVersionPage.value > 1) cvVersionPage.value -= 1
  }
  const nextCvVersionPage = () => {
    const lastPage = versionsQuery.data.value?.lastPage ?? 1
    if (cvVersionPage.value < lastPage) cvVersionPage.value += 1
  }

  function analyze(): void {
    if (!current.value || hasUnsavedChanges.value) return
    queryClient.removeQueries({ queryKey: jdQueryKeys.analysisRoot(jobDescriptionId.value) })
    analysisMutation.mutate({
      generation: routeGeneration.value,
      jobDescriptionId: current.value.id,
      revisionId: current.value.current_revision?.id ?? '',
      idempotencyKey: (pendingAnalysisKey ??= crypto.randomUUID()),
    })
  }

  function createReport(): void {
    if (!current.value || hasUnsavedChanges.value || !analysis.value || !selectedCvVersionId.value)
      return
    matchMutation.mutate({
      generation: routeGeneration.value,
      cvVersionId: selectedCvVersionId.value,
      jobDescriptionId: current.value.id,
      idempotencyKey: (pendingMatchKey ??= crypto.randomUUID()),
    })
  }

  function reloadCurrentRevision(): void {
    localError.value = ''
    saveMutation.reset()
    analysisMutation.reset()
    storedAnalysisId.value = ''
    void jdQuery.refetch().then(({ data }) => {
      if (data && data.id === jobDescriptionId.value) jdDraft.load(data)
    })
  }

  function runDelete(): void {
    const jd = current.value
    if (!jd) return
    deleteMutation.mutate({
      generation: routeGeneration.value,
      jobDescriptionId: jd.id,
      revisionId: jd.current_revision?.id ?? '',
      idempotencyKey: (pendingDeleteKey ??= crypto.randomUUID()),
    })
  }

  function confirmDelete(): void {
    if (
      typeof window === 'undefined' ||
      window.confirm(
        'Delete this saved Job Description? Existing historical reports remain readable.',
      )
    ) {
      runDelete()
    }
  }

  function retryMatch(): void {
    const jd = current.value
    if (!jd) return
    matchMutation.mutate({
      generation: routeGeneration.value,
      cvVersionId: selectedCvVersionId.value,
      jobDescriptionId: jd.id,
      idempotencyKey: (pendingMatchKey ??= crypto.randomUUID()),
    })
  }

  watch(
    jdQuery.data,
    (jd) => {
      if (!jd?.current_revision || jd.id !== jobDescriptionId.value) return
      if (saved.value?.id === jd.id && hasUnsavedChanges.value) return
      jdDraft.load(jd)
    },
    { immediate: true },
  )
  watch(
    current,
    (jd) => {
      const revisionId = jd?.current_revision?.id
      if (!revisionId || typeof window === 'undefined' || !window.localStorage) {
        storedAnalysisId.value = ''
        return
      }
      storedAnalysisId.value = window.localStorage.getItem(analysisStorageKey(revisionId)) ?? ''
    },
    { immediate: true },
  )
  watch([rawText, company, role], () => {
    if (!saveMutation.isPending.value) pendingSaveKey = null
  })
  watch(
    jobDescriptionId,
    (id) => {
      routeGeneration.value += 1
      if (id && createdRouteId.value === id) {
        createdRouteId.value = ''
        return
      }
      jdDraft.reset()
      localError.value = ''
      storedAnalysisId.value = ''
      selectedCvVersionId.value = ''
      cvVersionPage.value = 1
      pendingSaveKey = null
      pendingAnalysisKey = null
      pendingMatchKey = null
      pendingDeleteKey = null
      analysisMutation.reset()
      saveMutation.reset()
      matchMutation.reset()
      deleteMutation.reset()
      if (!id) void queryClient.removeQueries({ queryKey: jdQueryKeys.detail(id) })
    },
    { immediate: true },
  )
  watch(selectedCvVersionId, () => {
    if (!matchMutation.isPending.value) pendingMatchKey = null
  })
  watch(errorMessage, (message) => {
    if (!message) return
    void nextTick(() => {
      const field = firstValidationField()
      if (field) document.getElementById(field)?.focus()
      else errorSummary.value?.focus()
    })
  })
  watch(localError, (message) => {
    if (message) void nextTick(() => errorSummary.value?.focus())
  })

  return {
    jobDescriptionId,
    rawText,
    company,
    role,
    hasUnsavedChanges,
    localError,
    errorSummary,
    selectedCvVersionId,
    cvVersionPage,
    jdQuery,
    current,
    analysisQuery,
    analysis,
    versionsQuery,
    analysisMutation,
    saveMutation,
    matchMutation,
    deleteMutation,
    charCount,
    errorMessage,
    retryable,
    saveRetryable,
    deleteRetryable,
    saveConflict,
    deleteConflict,
    analysisConflict,
    matchRetryable,
    submit,
    limitCodePoints,
    setCompany,
    setRole,
    setRawText,
    setSelectedCvVersionId,
    setErrorSummary,
    previousCvVersionPage,
    nextCvVersionPage,
    analyze,
    createReport,
    reloadCurrentRevision,
    confirmDelete,
    retryMatch,
    retrySave: submit,
    retryDelete: runDelete,
    fieldError,
  }
}

export type JdEditorController = ReturnType<typeof useJdEditorController>
