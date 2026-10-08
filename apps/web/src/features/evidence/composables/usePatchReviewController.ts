import { computed, ref, watch } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRoute, useRouter } from 'vue-router'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'
import { approvePatch, editPatch, regeneratePatch, rejectPatch } from '../api/evidence.api'
import { evidenceQueryKeys } from '../api/evidence.keys'
import { usePatchQuery } from '../api/evidence.queries'
import type { Patch } from '../types/evidence.types'

type PatchAction = 'edit' | 'reject' | 'approve' | 'regenerate'

interface PatchMutationRequest {
  patchId: string
  revision: number
  idempotencyKey: string
  draft?: string
  generation: number
}

export function usePatchReviewController() {
  const route = useRoute()
  const router = useRouter()
  const queryClient = useQueryClient()
  const patchId = computed(() => String(route.params.patchId || ''))
  const legacyCvId = computed(() => String(route.params.id || ''))
  const draft = ref('')
  const idempotencyKeys = ref<Record<string, string>>({})
  const routeGeneration = ref(0)
  const initializedDraftPatchId = ref('')
  const query = usePatchQuery(patchId)
  const mutationKey = (action: string) =>
    (idempotencyKeys.value[action] ??= crypto.randomUUID())
  const clearMutationKey = (action: string, request: PatchMutationRequest) => {
    if (idempotencyKeys.value[action] === request.idempotencyKey) {
      delete idempotencyKeys.value[action]
    }
  }
  const refreshConflict = (request: PatchMutationRequest, error: unknown) => {
    if (
      request.generation === routeGeneration.value &&
      request.patchId === patchId.value &&
      error instanceof ApiRequestError &&
      error.status === 409
    )
      void query.refetch()
  }
  const cacheOrigin = (action: PatchAction, patch: Patch, request: PatchMutationRequest): string => {
    clearMutationKey(action, request)
    queryClient.setQueryData(evidenceQueryKeys.patch(request.patchId), patch)
    return request.patchId
  }
  const edit = useMutation({
    mutationFn: (request: PatchMutationRequest) =>
      editPatch(request.patchId, request.revision, request.draft ?? '', request.idempotencyKey),
    onSuccess: (patch, request) => {
      const originId = cacheOrigin('edit', patch, request)
      if (
        request.generation === routeGeneration.value &&
        originId === patchId.value &&
        patch.id === originId &&
        draft.value === request.draft
      ) draft.value = patch.new_value
    },
    onError: (error, request) => refreshConflict(request, error),
  })
  const reject = useMutation({
    mutationFn: (request: PatchMutationRequest) =>
      rejectPatch(request.patchId, request.revision, request.idempotencyKey),
    onSuccess: (patch, request) => cacheOrigin('reject', patch, request),
    onError: (error, request) => refreshConflict(request, error),
  })
  const approve = useMutation({
    mutationFn: (request: PatchMutationRequest) =>
      approvePatch(request.patchId, request.revision, request.idempotencyKey),
    onSuccess: (patch, request) => cacheOrigin('approve', patch, request),
    onError: (error, request) => refreshConflict(request, error),
  })
  const regenerate = useMutation({
    mutationFn: (request: PatchMutationRequest) =>
      regeneratePatch(request.patchId, request.revision, request.idempotencyKey),
    onSuccess: (patch, request) => {
      const originId = request.patchId
      clearMutationKey('regenerate', request)
      queryClient.setQueryData(evidenceQueryKeys.patch(patch.id), patch)
      if (
        request.generation === routeGeneration.value &&
        originId === patchId.value &&
        patch.predecessor_patch_id === originId
      ) {
        void router.push(ROUTES.PATCH_REVIEW(patch.id))
      }
    },
    onError: (error, request) => refreshConflict(request, error),
  })

  function confirmDecision(message: string, action: () => void): void {
    if (typeof window === 'undefined' || window.confirm(message)) action()
  }
  function isRetryable(error: unknown): boolean {
    return error instanceof ApiRequestError ? error.status === 429 || error.status >= 500 : true
  }
  function startEdit(): void {
    if (initializedDraftPatchId.value === patchId.value) return
    draft.value = typeof query.data.value?.new_value === 'string' ? query.data.value.new_value : ''
    initializedDraftPatchId.value = patchId.value
  }
  function runPatchMutation(action: PatchAction): void {
    const request: PatchMutationRequest = {
      patchId: patchId.value,
      revision: query.data.value?.revision ?? 0,
      idempotencyKey: mutationKey(action),
      generation: routeGeneration.value,
      ...(action === 'edit' ? { draft: draft.value } : {}),
    }
    if (action === 'edit') edit.mutate(request)
    else if (action === 'reject') reject.mutate(request)
    else if (action === 'approve') approve.mutate(request)
    else regenerate.mutate(request)
  }
  function sourceValue(value: Patch['old_value']): string {
    if (typeof value === 'string') return value
    return value === null ? 'No existing value' : 'Existing highlight collection (hash precondition)'
  }

  watch(patchId, () => {
    routeGeneration.value += 1
    draft.value = ''
    initializedDraftPatchId.value = ''
    idempotencyKeys.value = {}
    edit.reset()
    reject.reset()
    approve.reset()
    regenerate.reset()
  })

  return {
    patchId,
    legacyCvId,
    draft,
    query,
    edit,
    reject,
    approve,
    regenerate,
    confirmDecision,
    isRetryable,
    startEdit,
    runPatchMutation,
    sourceValue,
  }
}
