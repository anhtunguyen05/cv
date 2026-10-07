<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { ApiRequestError } from '@/shared/api/client'
import {
  analyzeJobDescription,
  createJobDescription,
  deleteJobDescription,
  getJobDescriptionAnalysis,
  getJobDescription,
  updateJobDescription,
} from '@/features/jd/api/jd.api'
import { getCvVersionsPage } from '@/features/cv/api/cv.api'
import { createMatchReport } from '@/features/match/api/match.api'
import type { JobDescription } from '@/features/jd/types/jd.types'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { ArrowLeft, RefreshCw, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const queryClient = useQueryClient()
const jobDescriptionId = computed(() => {
  const value = route.params.id
  return typeof value === 'string' && value !== 'new' ? value : ''
})
const rawText = ref('')
const company = ref('')
const role = ref('')
const saved = ref<JobDescription | null>(null)
const localError = ref('')
const errorSummary = ref<HTMLElement | null>(null)
const storedAnalysisId = ref('')
const selectedCvVersionId = ref('')
const cvVersionPage = ref(1)

const ANALYSIS_RULE_VERSION = '1.0.0'
function analysisStorageKey(revisionId: string, ruleVersion = ANALYSIS_RULE_VERSION): string {
  return `careerfitcv:analysis:${revisionId}:${ruleVersion}`
}

const jdQuery = useQuery({
  queryKey: computed(() => ['job-descriptions', jobDescriptionId.value]),
  queryFn: () => getJobDescription(jobDescriptionId.value),
  enabled: computed(() => jobDescriptionId.value.length > 0),
  retry: false,
})

watch(
  jdQuery.data,
  (jd) => {
    if (!jd?.current_revision) return
    saved.value = jd
    rawText.value = jd.current_revision.raw_text
    company.value = jd.current_revision.company ?? ''
    role.value = jd.current_revision.role ?? ''
  },
  { immediate: true },
)

const current = computed(() => saved.value ?? jdQuery.data.value ?? null)

watch(
  current,
  (jd) => {
    const revisionId = jd?.current_revision?.id
    if (!revisionId || typeof window === 'undefined') {
      storedAnalysisId.value = ''
      return
    }
    storedAnalysisId.value = window.localStorage.getItem(analysisStorageKey(revisionId)) ?? ''
  },
  { immediate: true },
)

const hasUnsavedChanges = computed(() => {
  const revision = current.value?.current_revision
  if (!revision) return false
  return (
    rawText.value !== revision.raw_text ||
    (company.value || null) !== revision.company ||
    (role.value || null) !== revision.role
  )
})

let pendingSaveKey: string | null = null
let pendingAnalysisKey: string | null = null
let pendingMatchKey: string | null = null
let pendingDeleteKey: string | null = null

const analysisMutation = useMutation({
  mutationFn: () =>
    analyzeJobDescription(current.value?.id ?? '', (pendingAnalysisKey ??= crypto.randomUUID())),
  onSuccess: (result) => {
    storedAnalysisId.value = result.id
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(
        analysisStorageKey(result.job_description_revision_id, result.analysis_rule_version),
        result.id,
      )
    }
    pendingAnalysisKey = null
  },
})

const analysisQuery = useQuery({
  queryKey: computed(() => [
    'job-descriptions',
    jobDescriptionId.value,
    'analysis',
    storedAnalysisId.value,
  ]),
  queryFn: () => getJobDescriptionAnalysis(jobDescriptionId.value, storedAnalysisId.value),
  enabled: computed(() => jobDescriptionId.value !== '' && storedAnalysisId.value !== ''),
  retry: false,
})
const analysis = computed(() => {
  const candidate = !storedAnalysisId.value
    ? analysisMutation.data.value
    : (analysisMutation.data.value ?? analysisQuery.data.value)
  if (!candidate || candidate.job_description_revision_id !== current.value?.current_revision?.id) {
    return undefined
  }

  return candidate
})
const versionsQuery = useQuery({
  queryKey: computed(() => ['cv-versions', cvVersionPage.value]),
  queryFn: () => getCvVersionsPage(cvVersionPage.value, 20),
  enabled: computed(() => Boolean(current.value && analysis.value)),
})
const charCount = computed(() => [...rawText.value].length)
const overLimit = computed(
  () => charCount.value > 50000 || new TextEncoder().encode(rawText.value).length > 204800,
)

const saveMutation = useMutation({
  mutationFn: () => {
    if (jobDescriptionId.value && current.value?.current_revision) {
      return updateJobDescription(
        jobDescriptionId.value,
        current.value.current_revision.id,
        {
          raw_text: rawText.value,
          company: company.value || null,
          role: role.value || null,
        },
        (pendingSaveKey ??= crypto.randomUUID()),
      )
    }
    return createJobDescription(
      { raw_text: rawText.value, company: company.value || null, role: role.value || null },
      (pendingSaveKey ??= crypto.randomUUID()),
    )
  },
  onSuccess: async (jd) => {
    const previousRevisionId = current.value?.current_revision?.id
    saved.value = jd
    analysisMutation.reset()
    storedAnalysisId.value = ''
    if (jobDescriptionId.value) {
      queryClient.removeQueries({
        queryKey: ['job-descriptions', jobDescriptionId.value, 'analysis'],
      })
    }
    if (typeof window !== 'undefined') {
      if (previousRevisionId) window.localStorage.removeItem(analysisStorageKey(previousRevisionId))
      if (jd.current_revision)
        window.localStorage.removeItem(analysisStorageKey(jd.current_revision.id))
    }
    await queryClient.invalidateQueries({ queryKey: ['job-descriptions'] })
    if (!jobDescriptionId.value) await router.replace(`/jd/${jd.id}`)
    pendingSaveKey = null
  },
})

const matchMutation = useMutation({
  mutationFn: () =>
    createMatchReport(
      selectedCvVersionId.value,
      current.value?.id ?? '',
      (pendingMatchKey ??= crypto.randomUUID()),
    ),
  onSuccess: async (report) => {
    await queryClient.invalidateQueries({ queryKey: ['match-reports'] })
    await router.push(`/match/${report.id}`)
    pendingMatchKey = null
  },
})

const deleteMutation = useMutation({
  mutationFn: () =>
    deleteJobDescription(
      current.value?.id ?? '',
      current.value?.current_revision?.id ?? '',
      (pendingDeleteKey ??= crypto.randomUUID()),
    ),
  onSuccess: async () => {
    const deletedId = current.value?.id
    pendingDeleteKey = null
    if (deletedId) queryClient.removeQueries({ queryKey: ['job-descriptions', deletedId] })
    await queryClient.invalidateQueries({ queryKey: ['job-descriptions'] })
    saved.value = null
    await router.push('/dashboard')
  },
})

watch([rawText, company, role], () => {
  if (!saveMutation.isPending.value) pendingSaveKey = null
})
watch(selectedCvVersionId, () => {
  if (!matchMutation.isPending.value) pendingMatchKey = null
})

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
watch(errorMessage, (message) => {
  if (!message) return
  void nextTick(() => {
    const field = firstValidationField()
    if (field) {
      document.getElementById(field)?.focus()
      return
    }
    errorSummary.value?.focus()
  })
})
watch(localError, (message) => {
  if (message) void nextTick(() => errorSummary.value?.focus())
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

function submit(): void {
  localError.value = ''
  if (!rawText.value.trim()) {
    localError.value = 'Paste a Job Description before saving.'
    return
  }
  if (overLimit.value) {
    localError.value = 'The Job Description must be at most 50,000 Unicode code points and 200 KiB.'
    return
  }
  saveMutation.mutate()
}

function limitCodePoints(value: string, limit: number): string {
  return [...value].slice(0, limit).join('')
}

function analyze(): void {
  if (current.value && !hasUnsavedChanges.value) {
    queryClient.removeQueries({
      queryKey: ['job-descriptions', jobDescriptionId.value, 'analysis'],
    })
    analysisMutation.mutate()
  }
}

function createReport(): void {
  if (current.value && !hasUnsavedChanges.value && analysis.value && selectedCvVersionId.value)
    matchMutation.mutate()
}

function reloadCurrentRevision(): void {
  localError.value = ''
  saveMutation.reset()
  analysisMutation.reset()
  storedAnalysisId.value = ''
  void jdQuery.refetch()
}

function confirmDelete(): void {
  if (
    window.confirm(
      'Delete this saved Job Description? Existing historical reports remain readable.',
    )
  ) {
    deleteMutation.mutate()
  }
}

function retryMatch(): void {
  matchMutation.mutate()
}

function retrySave(): void {
  saveMutation.mutate()
}

function retryDelete(): void {
  deleteMutation.mutate()
}

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
</script>

<template>
  <div class="space-y-6 sm:space-y-8" aria-live="polite">
    <div class="pb-4 border-b border-border/80">
      <RouterLink
        to="/dashboard"
        class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-text"
      >
        <ArrowLeft :size="14" aria-hidden="true" /> Dashboard
      </RouterLink>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text mt-3">
        {{ current ? 'Edit Job Description' : 'Save Job Description' }}
      </h1>
      <p class="text-xs sm:text-sm text-text-muted mt-1">
        Preserve the source exactly as pasted, then run the deterministic analysis when you are
        ready.
      </p>
    </div>

    <Card v-if="jdQuery.isLoading.value" aria-label="Loading Job Description"
      ><div class="h-40 animate-pulse rounded bg-surface-muted"
    /></Card>
    <Card v-else-if="jdQuery.isError.value" role="alert" class="space-y-3">
      <h2 class="font-bold text-text">Unable to load this Job Description</h2>
      <p class="text-sm text-text-muted">{{ errorMessage || 'The resource was not found.' }}</p>
      <AppButton v-if="retryable" variant="outline" type="button" @click="jdQuery.refetch()"
        ><RefreshCw :size="15" aria-hidden="true" /> Retry</AppButton
      >
    </Card>

    <template v-else>
      <form class="space-y-5" @submit.prevent="submit">
        <div
          v-if="localError || errorMessage"
          ref="errorSummary"
          role="alert"
          tabindex="-1"
          class="p-3 rounded-xl bg-danger-muted border border-danger-border text-sm text-danger-hover"
        >
          <p>{{ localError || errorMessage }}</p>
          <AppButton
            v-if="
              saveMutation.error.value instanceof ApiRequestError &&
              saveMutation.error.value.status === 409
            "
            type="button"
            variant="outline"
            class="mt-3"
            @click="reloadCurrentRevision"
            >Reload current revision</AppButton
          >
          <AppButton
            v-if="saveRetryable"
            type="button"
            variant="outline"
            class="mt-3"
            @click="retrySave"
            >Retry save</AppButton
          >
          <AppButton
            v-if="
              deleteMutation.error.value instanceof ApiRequestError &&
              deleteMutation.error.value.status === 409
            "
            type="button"
            variant="outline"
            class="mt-3"
            @click="reloadCurrentRevision"
            >Reload current revision</AppButton
          >
          <AppButton
            v-if="deleteRetryable"
            type="button"
            variant="outline"
            class="mt-3"
            @click="retryDelete"
            >Retry delete</AppButton
          >
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <label class="flex flex-col gap-1.5 text-sm font-semibold text-text" for="company"
            >Company <span class="text-xs font-normal text-text-muted">Optional</span
            ><input
              id="company"
              v-model="company"
              @input="company = limitCodePoints(company, 160)"
              :aria-invalid="Boolean(fieldError('company'))"
              :aria-describedby="fieldError('company') ? 'company-error' : undefined"
              class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
            /><span
              v-if="fieldError('company')"
              id="company-error"
              class="text-xs font-normal text-danger"
              >{{ fieldError('company') }}</span
            ></label
          >
          <label class="flex flex-col gap-1.5 text-sm font-semibold text-text" for="role"
            >Role <span class="text-xs font-normal text-text-muted">Optional</span
            ><input
              id="role"
              v-model="role"
              @input="role = limitCodePoints(role, 160)"
              :aria-invalid="Boolean(fieldError('role'))"
              :aria-describedby="fieldError('role') ? 'role-error' : undefined"
              class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
            /><span
              v-if="fieldError('role')"
              id="role-error"
              class="text-xs font-normal text-danger"
              >{{ fieldError('role') }}</span
            ></label
          >
        </div>
        <div class="space-y-2">
          <label for="jd-text" class="text-sm font-semibold text-text"
            >Job Description source <span class="text-danger">*</span></label
          >
          <textarea
            id="jd-text"
            v-model="rawText"
            @input="rawText = limitCodePoints(rawText, 50000)"
            rows="16"
            :aria-invalid="Boolean(fieldError('raw_text'))"
            :aria-describedby="fieldError('raw_text') ? 'jd-help jd-text-error' : 'jd-help'"
            class="w-full p-4 rounded-xl text-sm font-mono border border-border bg-white text-text focus:outline-none focus:ring-2 focus:ring-primary/20 leading-relaxed resize-y"
          />
          <div id="jd-help" class="flex justify-between text-xs text-text-muted">
            <span>{{ charCount }} / 50,000 Unicode code points</span
            ><span>Raw source is preserved after decoding.</span>
          </div>
          <p v-if="fieldError('raw_text')" id="jd-text-error" class="text-xs text-danger">
            {{ fieldError('raw_text') }}
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <AppButton
            type="submit"
            :loading="saveMutation.isPending.value"
            :disabled="saveMutation.isPending.value"
            >{{ current ? 'Save new revision' : 'Save Job Description' }}</AppButton
          >
          <AppButton
            v-if="current"
            type="button"
            variant="outline"
            :loading="analysisMutation.isPending.value"
            :disabled="analysisMutation.isPending.value || hasUnsavedChanges"
            @click="analyze"
            >Analyze revision</AppButton
          >
          <AppButton
            v-if="current"
            type="button"
            variant="destructive"
            :loading="deleteMutation.isPending.value"
            :disabled="deleteMutation.isPending.value"
            @click="confirmDelete"
            ><Trash2 :size="15" aria-hidden="true" /> Delete</AppButton
          >
        </div>
      </form>

      <Card
        v-if="analysisQuery.isError.value || analysisMutation.isError.value"
        class="space-y-3"
        role="alert"
      >
        <h2 class="text-lg font-bold text-text">Analysis unavailable</h2>
        <p class="text-sm text-text-muted">
          {{
            analysisMutation.error.value instanceof Error
              ? analysisMutation.error.value.message
              : analysisQuery.error.value instanceof Error
                ? analysisQuery.error.value.message
                : 'The analysis could not be loaded.'
          }}
        </p>
        <div class="flex flex-wrap gap-3">
          <AppButton
            v-if="analysisQuery.isError.value"
            type="button"
            variant="outline"
            @click="analysisQuery.refetch()"
            ><RefreshCw :size="15" aria-hidden="true" /> Retry analysis load</AppButton
          >
          <AppButton
            v-if="analysisQuery.isError.value || analysisMutation.isError.value"
            type="button"
            variant="outline"
            @click="analyze"
            >Analyze again</AppButton
          >
          <AppButton
            v-if="
              analysisMutation.error.value instanceof ApiRequestError &&
              analysisMutation.error.value.status === 409
            "
            type="button"
            variant="outline"
            @click="reloadCurrentRevision"
            >Reload current revision</AppButton
          >
        </div>
      </Card>

      <Card v-if="analysis" class="space-y-4" aria-live="polite">
        <div>
          <h2 class="text-lg font-bold text-text">Deterministic analysis</h2>
          <p class="text-xs text-text-muted">
            Analysis rule {{ analysis.analysis_rule_version }} · revision
            {{ analysis.job_description_revision_id }} · Analysis ID {{ analysis.id }}
          </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div
            v-for="(signal, name) in analysis.signals"
            :key="name"
            class="rounded-lg border border-border p-3"
          >
            <span class="font-semibold text-text capitalize">{{
              String(name).replaceAll('_', ' ')
            }}</span>
            <p class="text-xs text-text-muted mt-1">
              {{ signal.state
              }}<span v-if="'value' in signal && signal.value">: {{ signal.value }}</span
              ><span v-else-if="'items' in signal && signal.items.length"
                >:
                {{
                  signal.items
                    .map((item) => (typeof item === 'string' ? item : item.label))
                    .join(', ')
                }}</span
              >
            </p>
          </div>
        </div>

        <div class="rounded-xl border border-border bg-surface-muted/40 p-4 space-y-3">
          <div>
            <h3 class="text-sm font-bold text-text">Generate a Match Report</h3>
            <p class="text-xs text-text-muted">
              Choose a saved CV Version. The server pins this report to the current analyzed
              revision.
            </p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <label
              class="flex-1 flex flex-col gap-1.5 text-sm font-semibold text-text"
              for="cv-version"
              >CV Version
              <select
                id="cv-version"
                v-model="selectedCvVersionId"
                class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
              >
                <option value="">Select a CV Version</option>
                <option
                  v-for="version in versionsQuery.data.value?.items ?? []"
                  :key="version.id"
                  :value="version.id"
                >
                  {{ version.name }}
                </option>
              </select>
            </label>
            <AppButton
              type="button"
              :loading="matchMutation.isPending.value"
              :disabled="!selectedCvVersionId || matchMutation.isPending.value || hasUnsavedChanges"
              @click="createReport"
              >Create Match Report</AppButton
            >
          </div>
          <p v-if="versionsQuery.isLoading.value" class="text-xs text-text-muted">
            Loading CV Versions…
          </p>
          <div
            v-if="(versionsQuery.data.value?.lastPage ?? 1) > 1"
            class="flex items-center justify-between text-xs text-text-muted"
          >
            <button
              type="button"
              class="underline"
              :disabled="cvVersionPage <= 1"
              @click="cvVersionPage -= 1"
            >
              Previous versions
            </button>
            <span>Page {{ cvVersionPage }} of {{ versionsQuery.data.value?.lastPage }}</span>
            <button
              type="button"
              class="underline"
              :disabled="cvVersionPage >= (versionsQuery.data.value?.lastPage ?? 1)"
              @click="cvVersionPage += 1"
            >
              Next versions
            </button>
          </div>
          <p v-else-if="versionsQuery.isError.value" class="text-xs text-danger">
            Unable to load CV Versions.
            <button type="button" class="underline" @click="versionsQuery.refetch()">Retry</button>
          </p>
          <p v-if="hasUnsavedChanges" class="text-xs text-warning-text" role="status">
            Save this edited revision before analyzing or matching.
          </p>
          <p v-if="matchMutation.isError.value" class="text-xs text-danger" role="alert">
            {{
              matchMutation.error.value instanceof Error
                ? matchMutation.error.value.message
                : 'The Match Report could not be created.'
            }}
            <button
              v-if="
                matchMutation.error.value instanceof ApiRequestError &&
                [409, 429, 503].includes(matchMutation.error.value.status)
              "
              type="button"
              class="underline"
              @click="retryMatch"
            >
              Retry
            </button>
          </p>
        </div>
      </Card>
    </template>
  </div>
</template>
