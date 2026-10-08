import { computed, ref, watch } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRoute, useRouter } from 'vue-router'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'
import { generatePatch, submitEvidenceAnswer } from '../api/evidence.api'
import { evidenceQueryKeys } from '../api/evidence.keys'
import { useEvidenceInterviewQuery } from '../api/evidence.queries'

export const interviewStatusLabels = {
  active: 'Active',
  completed: 'Completed',
  expired: 'Expired',
} as const
export const interviewStatusVariants = {
  active: 'success',
  completed: 'muted',
  expired: 'warning',
} as const

export function useAiInterviewController() {
  const route = useRoute()
  const router = useRouter()
  const queryClient = useQueryClient()
  const interviewId = computed(() => String(route.params.id || ''))
  const interviewQuery = useEvidenceInterviewQuery(interviewId)
  const answers = ref<Record<string, string>>({})
  const answerKeys = ref<Record<string, string>>({})
  const answerPayloads = ref<Record<string, string>>({})
  const routeGeneration = ref(0)
  const activeQuestion = ref<string | null>(null)
  interface AnswerRequest {
    interviewId: string
    questionId: string
    answer?: string
    cannotProvide?: boolean
    idempotencyKey: string
    generation: number
  }
  const answerMutation = useMutation({
    mutationFn: (input: AnswerRequest) =>
      submitEvidenceAnswer(
        input.interviewId,
        {
          question_id: input.questionId,
          question_version: '1.0',
          outcome: input.cannotProvide ? 'cannot_provide' : 'answer',
          ...(input.cannotProvide ? {} : { answer: input.answer ?? '' }),
        },
        input.idempotencyKey,
      ),
    onSuccess: ({ interview }, input) => {
      queryClient.setQueryData(evidenceQueryKeys.interview(input.interviewId), interview)
      if (answerKeys.value[input.questionId] === input.idempotencyKey) {
        delete answerKeys.value[input.questionId]
        delete answerPayloads.value[input.questionId]
      }
      if (
        input.generation === routeGeneration.value &&
        input.interviewId === interviewId.value &&
        interview.id === input.interviewId
      ) {
        activeQuestion.value = null
      }
    },
  })
  const generationKey = ref<string | null>(null)
  interface GenerationRequest {
    interviewId: string
    idempotencyKey: string
    generation: number
  }
  const generationMutation = useMutation({
    mutationFn: (input: GenerationRequest) =>
      generatePatch(input.interviewId, input.idempotencyKey),
    onSuccess: (patch, input) => {
      if (generationKey.value === input.idempotencyKey) generationKey.value = null
      if (
        input.generation === routeGeneration.value &&
        input.interviewId === interviewId.value &&
        patch.interview_id === input.interviewId
      ) {
        void router.push(ROUTES.PATCH_REVIEW(patch.id))
      }
    },
  })
  const retryable = computed(() => {
    const error = interviewQuery.error.value
    return error instanceof ApiRequestError ? error.status === 429 || error.status >= 500 : true
  })

  function submitAnswer(questionId: string, cannotProvide = false): void {
    activeQuestion.value = questionId
    const signature = JSON.stringify({ answer: answers.value[questionId] ?? '', cannotProvide })
    if (answerPayloads.value[questionId] !== signature) {
      answerKeys.value[questionId] = crypto.randomUUID()
      answerPayloads.value[questionId] = signature
    }
    const idempotencyKey = answerKeys.value[questionId] ??= crypto.randomUUID()
    answerMutation.mutate({
      interviewId: interviewId.value,
      questionId,
      answer: answers.value[questionId],
      cannotProvide,
      idempotencyKey,
      generation: routeGeneration.value,
    })
  }

  function generatePatchProposal(): void {
    generationMutation.mutate({
      interviewId: interviewId.value,
      idempotencyKey: (generationKey.value ??= crypto.randomUUID()),
      generation: routeGeneration.value,
    })
  }

  watch(interviewId, () => {
    routeGeneration.value += 1
    answers.value = {}
    answerKeys.value = {}
    answerPayloads.value = {}
    generationKey.value = null
    activeQuestion.value = null
    answerMutation.reset()
    generationMutation.reset()
  })

  return {
    interviewQuery,
    answers,
    activeQuestion,
    answerMutation,
    generationMutation,
    retryable,
    submitAnswer,
    generatePatchProposal,
  }
}
