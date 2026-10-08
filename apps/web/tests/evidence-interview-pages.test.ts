import { nextTick, reactive, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const queryState = {
  isLoading: ref(false),
  isError: ref(false),
  data: ref<unknown>(null),
  error: ref<unknown>(null),
  isFetching: ref(false),
  refetch: vi.fn(),
}
const mutationState = {
  isPending: ref(false),
  isError: ref(false),
  error: ref<unknown>(null),
  mutate: vi.fn(),
  reset: vi.fn(),
}
const push = vi.fn()
const setQueryData = vi.fn()
const mutationOptions: Array<Record<string, (...args: never[]) => unknown>> = []
const routeParams = reactive({
  id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  matchId: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  patchId: '01ARZ3NDEKTSV4RRFFQ69G5FB2',
})

vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn(() => queryState),
  useMutation: vi.fn((options) => {
    mutationOptions.push(options)
    return mutationState
  }),
  useQueryClient: vi.fn(() => ({ setQueryData })),
}))

vi.mock('vue-router', () => ({
  RouterLink: { template: '<a><slot /></a>' },
  useRoute: vi.fn(() => ({
    params: routeParams,
  })),
  useRouter: vi.fn(() => ({ push })),
}))

vi.mock('@/features/evidence/api/evidence.api', () => ({
  getEvidenceInterview: vi.fn(),
  startEvidenceInterview: vi.fn(),
  submitEvidenceAnswer: vi.fn(),
  generatePatch: vi.fn(),
  getPatch: vi.fn(),
  editPatch: vi.fn(),
  rejectPatch: vi.fn(),
  approvePatch: vi.fn(),
  regeneratePatch: vi.fn(),
}))
vi.mock('@/features/match/api/match.api', () => ({ getMatchReport: vi.fn() }))

import AiInterviewPage from '@/pages/ai/AiInterviewPage.vue'
import PatchReviewPage from '@/pages/cv/PatchReviewPage.vue'
import MatchReportPage from '@/pages/match/MatchReportPage.vue'

const interview = {
  id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  match_report_id: '01ARZ3NDEKTSV4RRFFQ69G5FAW',
  cv_version_id: '01ARZ3NDEKTSV4RRFFQ69G5FAX',
  job_description_id: '01ARZ3NDEKTSV4RRFFQ69G5FAY',
  job_description_revision_id: '01ARZ3NDEKTSV4RRFFQ69G5FAZ',
  analysis_id: '01ARZ3NDEKTSV4RRFFQ69G5FB0',
  areas: [
    {
      signal_id: 'typescript',
      label: 'TypeScript',
      importance: 'required',
      evidence_level: 'missing',
      source_references: [],
    },
  ],
  questions: [
    {
      id: '01ARZ3NDEKTSV4RRFFQ69G5FB1',
      area_signal_id: 'typescript',
      question_version: '1.0',
      question: 'Tell me about TypeScript.',
    },
  ],
  question_set_version: '1.0' as const,
  status: 'expired' as const,
  expires_at: '2026-10-14T00:00:00.000Z',
  created_at: '2026-10-07T00:00:00.000Z',
  updated_at: '2026-10-07T00:00:00.000Z',
}

const stubs = {
  Card: { template: '<section><slot /></section>' },
  Button: { props: ['disabled'], template: '<button :disabled="disabled"><slot /></button>' },
  AppBadge: { props: ['label'], template: '<span>{{ label }}</span>' },
  MatchScoreRing: { template: '<div />' },
  SkillGapList: { template: '<div />' },
}

const patch = {
  id: '01ARZ3NDEKTSV4RRFFQ69G5FB2',
  source_cv_version_id: interview.cv_version_id,
  match_report_id: interview.match_report_id,
  interview_id: interview.id,
  predecessor_patch_id: null,
  status: 'pending' as const,
  allowed_actions: ['edit', 'reject', 'approve'] as const,
  revision: 1,
  patch_schema_version: '1.0',
  prompt_version: 'fake-1.0',
  provider_model_version: 'deterministic-fake-1.0',
  target: {
    section: 'summary' as const,
    field: 'summary' as const,
    item_id: null,
    operation: 'replace' as const,
  },
  old_value: null,
  new_value: 'Built reliable software.',
  reason: 'Grounded in User Evidence.',
  evidence_source_ids: ['01ARZ3NDEKTSV4RRFFQ69G5FB3'],
  provenance: {},
  applied_version_id: null,
  created_at: interview.created_at,
  updated_at: interview.updated_at,
  evidence: [],
}

describe('Evidence interview pages', () => {
  beforeEach(() => {
    queryState.isLoading.value = false
    queryState.isError.value = false
    queryState.error.value = null
    queryState.isFetching.value = false
    queryState.refetch.mockReset()
    mutationState.isPending.value = false
    mutationState.isError.value = false
    mutationState.error.value = null
    mutationState.mutate.mockReset()
    mutationState.reset.mockReset()
    push.mockReset()
    setQueryData.mockReset()
    mutationOptions.length = 0
    routeParams.id = interview.id
    routeParams.matchId = interview.id
    routeParams.patchId = patch.id
  })

  it('shows closed-session guidance for an expired interview', () => {
    queryState.data.value = interview

    const wrapper = mount(AiInterviewPage, { global: { stubs } })

    expect(wrapper.text()).toContain('Expired')
    expect(wrapper.text()).toContain('cannot accept new answers')
    expect(wrapper.find('[role="status"]').exists()).toBe(true)
    wrapper.unmount()
  })

  it('shows the server cap in the Match Report CTA', () => {
    queryState.data.value = {
      overall_score: 70,
      matching_rule_version: '1.0.0',
      cv_version_id: interview.cv_version_id,
      job_description_id: interview.job_description_id,
      job_description_revision_id: interview.job_description_revision_id,
      analysis_id: interview.analysis_id,
      analysis_rule_version: '1.0.0',
      source_summary: {
        role: 'Engineer',
        company: 'Example',
        source_deleted: false,
        source_is_current: true,
      },
      matched_skills: [],
      missing_skills: Array.from({ length: 6 }, (_, index) => ({
        signal_id: `missing-${index}`,
        label: `Missing ${index}`,
        importance: 'required',
        evidence_level: 'missing',
        source_references: [],
      })),
      weak_evidence: [],
      recommendations: [],
    }

    const wrapper = mount(MatchReportPage, { global: { stubs } })

    expect(wrapper.text()).toContain('the first 5 will be included')
    wrapper.unmount()
  })

  it('shows server-derived Patch actions and a source/proposal diff', () => {
    queryState.data.value = patch

    const wrapper = mount(PatchReviewPage, { global: { stubs } })

    expect(wrapper.text()).toContain('Current CV source')
    expect(wrapper.text()).toContain('Provider proposal')
    expect(wrapper.text()).toContain('Save edit')
    expect(wrapper.text()).toContain('Approve into new CV Version')
    wrapper.unmount()
  })

  it('ignores a Patch-generation callback after the interview route changes', async () => {
    queryState.data.value = { ...interview, status: 'completed' }
    const wrapper = mount(AiInterviewPage, { global: { stubs } })
    const generate = wrapper.findAll('button').find((button) =>
      button.text().includes('Generate Patch proposal'),
    )

    await generate?.trigger('click')
    const request = mutationState.mutate.mock.calls[0]?.[0]
    routeParams.id = '01ARZ3NDEKTSV4RRFFQ69G5FC0'
    mutationOptions[1]?.onSuccess?.(patch as never, request)

    expect(push).not.toHaveBeenCalled()
    wrapper.unmount()
  })

  it('invalidates interview action keys across an A-to-B-to-A route cycle', async () => {
    queryState.data.value = { ...interview, status: 'active' }
    const wrapper = mount(AiInterviewPage, { global: { stubs } })
    const answer = wrapper.get(`#answer-${interview.questions[0].id}`)
    await answer.setValue('First payload')
    const saveAnswer = wrapper.findAll('button').find((button) => button.text() === 'Save answer')
    await saveAnswer?.trigger('click')
    const requestA = mutationState.mutate.mock.calls[0]?.[0]

    routeParams.id = '01ARZ3NDEKTSV4RRFFQ69G5FC0'
    await nextTick()
    routeParams.id = interview.id
    await nextTick()
    await wrapper.get(`#answer-${interview.questions[0].id}`).setValue('First payload')
    await saveAnswer?.trigger('click')
    const requestB = mutationState.mutate.mock.calls[1]?.[0]

    expect(requestB.idempotencyKey).not.toBe(requestA.idempotencyKey)
    expect(requestB.generation).toBeGreaterThan(requestA.generation)
    wrapper.unmount()
  })

  it('uses a fresh answer key when the retry payload changes', async () => {
    queryState.data.value = { ...interview, status: 'active' }
    const wrapper = mount(AiInterviewPage, { global: { stubs } })
    const answer = wrapper.get(`#answer-${interview.questions[0].id}`)
    const saveAnswer = wrapper.findAll('button').find((button) => button.text() === 'Save answer')

    await answer.setValue('First payload')
    await saveAnswer?.trigger('click')
    await answer.setValue('Changed payload')
    await saveAnswer?.trigger('click')

    expect(mutationState.mutate.mock.calls[1]?.[0].idempotencyKey).not.toBe(
      mutationState.mutate.mock.calls[0]?.[0].idempotencyKey,
    )
    wrapper.unmount()
  })

  it('updates only the originating Patch cache after the route changes', async () => {
    queryState.data.value = patch
    const wrapper = mount(PatchReviewPage, { global: { stubs } })
    const saveEdit = wrapper.findAll('button').find((button) => button.text() === 'Save edit')

    await saveEdit?.trigger('click')
    const request = mutationState.mutate.mock.calls[0]?.[0]
    routeParams.patchId = '01ARZ3NDEKTSV4RRFFQ69G5FC1'
    mutationOptions[0]?.onSuccess?.({ ...patch, new_value: 'Server response' } as never, request)

    expect(setQueryData).toHaveBeenCalledWith(
      ['patches', patch.id],
      expect.objectContaining({ id: patch.id }),
    )
    expect(wrapper.get('#patch-edit').element.value).not.toBe('Server response')
    wrapper.unmount()
  })

  it('keeps overlapping Patch callbacks scoped to each invocation resource', async () => {
    queryState.data.value = patch
    const wrapper = mount(PatchReviewPage, { global: { stubs } })
    const saveEdit = wrapper.findAll('button').find((button) => button.text() === 'Save edit')

    await saveEdit?.trigger('click')
    const requestA = mutationState.mutate.mock.calls[0]?.[0]
    routeParams.patchId = '01ARZ3NDEKTSV4RRFFQ69G5FC1'
    await saveEdit?.trigger('click')
    const requestB = mutationState.mutate.mock.calls[1]?.[0]

    mutationOptions[0]?.onSuccess?.({ ...patch, new_value: 'Response A' } as never, requestA)
    mutationOptions[0]?.onSuccess?.(
      { ...patch, id: requestB.patchId, new_value: 'Response B' } as never,
      requestB,
    )

    expect(setQueryData).toHaveBeenCalledWith(['patches', patch.id], expect.any(Object))
    expect(setQueryData).toHaveBeenCalledWith(['patches', requestB.patchId], expect.any(Object))
    wrapper.unmount()
  })

  it('preserves a newer Patch edit entered while an edit request is pending', async () => {
    queryState.data.value = patch
    const wrapper = mount(PatchReviewPage, { global: { stubs } })
    const editInput = wrapper.get('#patch-edit')
    await editInput.trigger('focus')
    await editInput.setValue('Submitted edit')
    const saveEdit = wrapper.findAll('button').find((button) => button.text() === 'Save edit')
    await saveEdit?.trigger('click')
    const request = mutationState.mutate.mock.calls[0]?.[0]
    await editInput.setValue('Newer local edit')

    mutationOptions[0]?.onSuccess?.({ ...patch, new_value: 'Submitted edit' } as never, request)

    expect(editInput.element.value).toBe('Newer local edit')
    wrapper.unmount()
  })

  it('ignores a Match interview callback after an A-to-B-to-A route cycle', async () => {
    queryState.data.value = {
      overall_score: 70,
      matching_rule_version: '1.0.0',
      cv_version_id: interview.cv_version_id,
      job_description_id: interview.job_description_id,
      job_description_revision_id: interview.job_description_revision_id,
      analysis_id: interview.analysis_id,
      analysis_rule_version: '1.0.0',
      source_summary: {
        role: 'Engineer',
        company: 'Example',
        source_deleted: false,
        source_is_current: true,
      },
      matched_skills: [],
      missing_skills: [interview.areas[0]],
      weak_evidence: [],
      recommendations: [],
    }
    const wrapper = mount(MatchReportPage, { global: { stubs } })
    const start = wrapper.findAll('button').find((button) =>
      button.text().includes('Start Evidence interview'),
    )
    await start?.trigger('click')
    const request = mutationState.mutate.mock.calls[0]?.[0]

    routeParams.matchId = '01ARZ3NDEKTSV4RRFFQ69G5FC0'
    await nextTick()
    routeParams.matchId = interview.id
    await nextTick()
    mutationOptions[0]?.onSuccess?.(interview as never, request)

    expect(push).not.toHaveBeenCalled()
    expect(mutationState.reset).toHaveBeenCalledTimes(2)
    wrapper.unmount()
  })
})
