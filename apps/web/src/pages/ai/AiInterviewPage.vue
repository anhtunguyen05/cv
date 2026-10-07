<script setup lang="ts">
import { computed, ref } from 'vue'
import { useMutation, useQuery } from '@tanstack/vue-query'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeft, RefreshCw, ShieldCheck } from 'lucide-vue-next'
import { getEvidenceInterview, generatePatch, submitEvidenceAnswer } from '@/features/evidence/api/evidence.api'
import { ApiRequestError } from '@/shared/api/client'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { ROUTES } from '@/shared/constants/routes'

const route = useRoute()
const interviewId = computed(() => String(route.params.id || ''))
const interviewQuery = useQuery({
  queryKey: computed(() => ['evidence-interviews', interviewId.value]),
  queryFn: () => getEvidenceInterview(interviewId.value),
  enabled: computed(() => interviewId.value.length > 0),
  retry: (count, error) => isRetryable(error) && count < 2,
})
const answers = ref<Record<string, string>>({})
const answerKeys = ref<Record<string, string>>({})
const activeQuestion = ref<string | null>(null)
const answerMutation = useMutation({
  mutationFn: (input: { questionId: string; answer?: string; cannotProvide?: boolean }) => submitEvidenceAnswer(interviewId.value, {
    question_id: input.questionId,
    question_version: '1.0',
    outcome: input.cannotProvide ? 'cannot_provide' : 'answer',
    ...(input.cannotProvide ? {} : { answer: input.answer ?? '' }),
  }, answerKeys.value[input.questionId] ??= crypto.randomUUID()),
  onSuccess: ({ interview }, input) => {
    interviewQuery.data.value = interview
    delete answerKeys.value[input.questionId]
    activeQuestion.value = null
  },
})
const generationKey = ref<string | null>(null)
const generationMutation = useMutation({
  mutationFn: () => generatePatch(interviewId.value, generationKey.value ??= crypto.randomUUID()),
  onSuccess: (patch) => {
    generationKey.value = null
    void router.push(ROUTES.PATCH_REVIEW(patch.id))
  },
})
const router = useRouter()

const retryable = computed(() => {
  const error = interviewQuery.error.value
  return isRetryable(error)
})

function isRetryable(error: unknown): boolean {
  return error instanceof ApiRequestError ? error.status === 429 || error.status >= 500 : true
}

function submitAnswer(questionId: string, cannotProvide = false): void {
  activeQuestion.value = questionId
  answerKeys.value[questionId] ??= crypto.randomUUID()
  answerMutation.mutate({ questionId, answer: answers.value[questionId], cannotProvide })
}

const statusLabels = {
  active: 'Active',
  completed: 'Completed',
  expired: 'Expired',
} as const
const statusVariants = {
  active: 'success',
  completed: 'muted',
  expired: 'warning',
} as const
</script>

<template>
  <div class="space-y-6 max-w-3xl mx-auto" aria-live="polite">
    <div class="space-y-2.5 pb-3 border-b border-border/80">
      <RouterLink
        :to="interviewQuery.data.value ? ROUTES.MATCH_REPORT(interviewQuery.data.value.match_report_id) : ROUTES.MATCH_REPORTS"
        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-text-muted hover:text-text transition-colors"
      >
        <ArrowLeft :size="15" aria-hidden="true" />
        <span>Back to Match Report</span>
      </RouterLink>
      <div class="flex items-start gap-3 pt-0.5">
        <ShieldCheck :size="24" class="text-primary mt-1 flex-shrink-0" aria-hidden="true" />
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">Evidence interview</h1>
          <p class="text-xs sm:text-sm text-text-muted mt-1 leading-relaxed">
            These questions are selected from the stored Match Report. Your answers will be handled as
            User Evidence and never change the source CV Version.
          </p>
        </div>
      </div>
    </div>

    <Card v-if="interviewQuery.isLoading.value" class="space-y-4" aria-label="Loading Evidence interview">
      <div class="h-6 w-48 animate-pulse rounded bg-surface-muted" />
      <div class="h-24 animate-pulse rounded bg-surface-muted" />
    </Card>

    <Card v-else-if="interviewQuery.isError.value" role="alert" class="space-y-3">
      <h2 class="text-lg font-bold text-text">Unable to load this interview</h2>
      <p class="text-sm text-text-muted">
        {{
          interviewQuery.error.value instanceof Error
            ? interviewQuery.error.value.message
            : 'The interview could not be loaded.'
        }}
      </p>
      <AppButton v-if="retryable" type="button" variant="outline" @click="interviewQuery.refetch()">
        <RefreshCw :size="15" aria-hidden="true" /> Retry
      </AppButton>
      <RouterLink
        v-else
        :to="ROUTES.MATCH_REPORTS"
        class="inline-flex items-center text-sm font-semibold text-primary hover:underline"
      >
        Return to Match Reports
      </RouterLink>
    </Card>

    <template v-else-if="interviewQuery.data.value">
      <Card class="space-y-4" aria-labelledby="interview-status-title">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 id="interview-status-title" class="text-lg font-bold text-text">Your Evidence prompts</h2>
            <p class="text-xs sm:text-sm text-text-muted">
              {{ interviewQuery.data.value.questions.length }} targeted question{{ interviewQuery.data.value.questions.length === 1 ? '' : 's' }}
              · Question set {{ interviewQuery.data.value.question_set_version }}
            </p>
            <p class="text-xs text-text-muted" role="status">
              {{ interviewQuery.data.value.progress?.answered ?? 0 }} of {{ interviewQuery.data.value.progress?.total ?? interviewQuery.data.value.questions.length }} outcomes saved
            </p>
          </div>
          <AppBadge
            :label="statusLabels[interviewQuery.data.value.status]"
            :variant="statusVariants[interviewQuery.data.value.status]"
            size="sm"
          />
        </div>

        <div class="space-y-3" role="list" aria-label="Evidence interview questions">
          <article
            v-for="(question, index) in interviewQuery.data.value.questions"
            :key="question.id"
            role="listitem"
            class="rounded-xl border border-border bg-white p-4 space-y-2"
          >
            <p class="text-xs font-bold uppercase tracking-wider text-text-muted">
              Question {{ index + 1 }} · {{
                interviewQuery.data.value.areas.find((area) => area.signal_id === question.area_signal_id)?.label ||
                'Unresolved area'
              }}
            </p>
            <h3 class="text-sm sm:text-base font-semibold text-text">{{ question.question }}</h3>
            <template v-if="interviewQuery.data.value.status === 'active' && !(interviewQuery.data.value.answers ?? []).some((answer) => answer.question_id === question.id)">
              <label :for="`answer-${question.id}`" class="sr-only">Your answer</label>
              <textarea
                :id="`answer-${question.id}`"
                v-model="answers[question.id]"
                rows="4"
                maxlength="4000"
                class="w-full rounded-xl border border-border bg-white p-3 text-sm text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Share a specific, truthful example."
              />
              <div class="flex flex-wrap gap-2">
                <AppButton type="button" size="sm" :loading="activeQuestion === question.id && answerMutation.isPending.value" :disabled="answerMutation.isPending.value" @click="submitAnswer(question.id)">
                  Save answer
                </AppButton>
                <AppButton type="button" size="sm" variant="outline" :disabled="answerMutation.isPending.value" @click="submitAnswer(question.id, true)">
                  I cannot provide evidence
                </AppButton>
              </div>
            </template>
            <p v-else-if="(interviewQuery.data.value.answers ?? []).some((answer) => answer.question_id === question.id)" class="text-xs text-text-muted" role="status">
              This outcome is saved as immutable User Evidence.
            </p>
          </article>
        </div>

        <p v-if="interviewQuery.data.value.status === 'active'" class="text-xs text-text-muted border-t border-border pt-3">
          This session expires {{ new Date(interviewQuery.data.value.expires_at).toLocaleDateString() }}.
          Answer submission will be available from this server-owned session.
        </p>
        <p v-else class="text-xs text-text-muted border-t border-border pt-3" role="status">
          This interview is {{ interviewQuery.data.value.status }} and cannot accept new answers.
          Return to the Match Report to start a fresh interview when the report has unresolved areas.
        </p>
        <div v-if="answerMutation.isError.value" class="flex flex-wrap items-center gap-3 border-t border-border pt-3" role="alert">
          <p class="text-sm text-danger-text">The answer could not be saved. Refresh the interview to reconcile its server-owned state.</p>
          <AppButton type="button" size="sm" variant="outline" @click="interviewQuery.refetch()">
            <RefreshCw :size="15" aria-hidden="true" /> Refresh interview
          </AppButton>
        </div>
        <div v-if="interviewQuery.data.value.status === 'completed'" class="border-t border-border pt-4 space-y-2">
          <p class="text-sm text-text-muted">All outcomes are recorded. Generate one bounded Patch proposal for your review.</p>
          <AppButton type="button" :loading="generationMutation.isPending.value" :disabled="generationMutation.isPending.value" @click="generationMutation.mutate()">
            Generate Patch proposal
          </AppButton>
          <p v-if="generationMutation.isError.value" class="text-sm text-danger-text" role="alert">The proposal could not be generated. You can retry safely.</p>
        </div>
      </Card>
    </template>
  </div>
</template>
