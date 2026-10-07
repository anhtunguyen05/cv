import { ref } from 'vue'
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

vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn(() => queryState),
  useMutation: vi.fn(() => mutationState),
}))

vi.mock('vue-router', () => ({
  RouterLink: { template: '<a><slot /></a>' },
  useRoute: vi.fn(() => ({ params: { id: '01ARZ3NDEKTSV4RRFFQ69G5FAV', matchId: '01ARZ3NDEKTSV4RRFFQ69G5FAV' } })),
  useRouter: vi.fn(() => ({ push })),
}))

vi.mock('@/features/evidence/api/evidence.api', () => ({
  getEvidenceInterview: vi.fn(),
  startEvidenceInterview: vi.fn(),
}))
vi.mock('@/features/match/api/match.api', () => ({ getMatchReport: vi.fn() }))

import AiInterviewPage from '@/pages/ai/AiInterviewPage.vue'
import MatchReportPage from '@/pages/match/MatchReportPage.vue'

const interview = {
  id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
  match_report_id: '01ARZ3NDEKTSV4RRFFQ69G5FAW',
  cv_version_id: '01ARZ3NDEKTSV4RRFFQ69G5FAX',
  job_description_id: '01ARZ3NDEKTSV4RRFFQ69G5FAY',
  job_description_revision_id: '01ARZ3NDEKTSV4RRFFQ69G5FAZ',
  analysis_id: '01ARZ3NDEKTSV4RRFFQ69G5FB0',
  areas: [{ signal_id: 'typescript', label: 'TypeScript', importance: 'required', evidence_level: 'missing', source_references: [] }],
  questions: [{ id: '01ARZ3NDEKTSV4RRFFQ69G5FB1', area_signal_id: 'typescript', question_version: '1.0', question: 'Tell me about TypeScript.' }],
  question_set_version: '1.0' as const,
  status: 'expired' as const,
  expires_at: '2026-10-14T00:00:00.000Z',
  created_at: '2026-10-07T00:00:00.000Z',
  updated_at: '2026-10-07T00:00:00.000Z',
}

const stubs = {
  Card: { template: '<section><slot /></section>' },
  AppButton: { props: ['disabled'], template: '<button :disabled="disabled"><slot /></button>' },
  AppBadge: { props: ['label'], template: '<span>{{ label }}</span>' },
  MatchScoreRing: { template: '<div />' },
  SkillGapList: { template: '<div />' },
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
      source_summary: { role: 'Engineer', company: 'Example', source_deleted: false, source_is_current: true },
      matched_skills: [],
      missing_skills: Array.from({ length: 6 }, (_, index) => ({ signal_id: `missing-${index}`, label: `Missing ${index}`, importance: 'required', evidence_level: 'missing', source_references: [] })),
      weak_evidence: [],
      recommendations: [],
    }

    const wrapper = mount(MatchReportPage, { global: { stubs } })

    expect(wrapper.text()).toContain('the first 5 will be included')
  })
})
