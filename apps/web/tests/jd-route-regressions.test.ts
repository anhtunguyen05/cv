import { reactive, ref } from 'vue'
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const routeParams = reactive({ id: 'JD-A' })
const replace = vi.fn()
const push = vi.fn()
const mutationOptions: Array<Record<string, (...args: never[]) => unknown>> = []
const mutationStates: Array<{
  isPending: ReturnType<typeof ref<boolean>>
  isError: ReturnType<typeof ref<boolean>>
  error: ReturnType<typeof ref<unknown>>
  data: ReturnType<typeof ref<unknown>>
  mutate: ReturnType<typeof vi.fn>
  reset: ReturnType<typeof vi.fn>
}> = []

const jobDescription = {
  id: 'JD-A',
  company: 'Company A',
  role: 'Engineer',
  current_revision: {
    id: 'REV-A',
    job_description_id: 'JD-A',
    revision_number: 1,
    raw_text: 'Source A',
    company: 'Company A',
    role: 'Engineer',
    created_at: '2026-10-08T00:00:00Z',
  },
  deleted_at: null,
  created_at: '2026-10-08T00:00:00Z',
  updated_at: '2026-10-08T00:00:00Z',
}

vi.mock('vue-router', () => ({
  RouterLink: { template: '<a><slot /></a>' },
  useRoute: () => ({ params: routeParams }),
  useRouter: () => ({ replace, push }),
}))

vi.mock('@tanstack/vue-query', () => ({
  useQueryClient: () => ({
    setQueryData: vi.fn(),
    removeQueries: vi.fn(),
    invalidateQueries: vi.fn(),
  }),
  useMutation: (options: Record<string, (...args: never[]) => unknown>) => {
    mutationOptions.push(options)
    const state = {
      isPending: ref(false),
      isError: ref(false),
      error: ref<unknown>(null),
      data: ref<unknown>(),
      mutate: vi.fn(),
      reset: vi.fn(),
    }
    mutationStates.push(state)
    return state
  },
}))

vi.mock('@/features/jd/api/jd.queries', () => ({
  useJobDescriptionQuery: () => ({
    data: ref(jobDescription),
    isLoading: ref(false),
    isError: ref(false),
    error: ref(null),
    refetch: vi.fn(),
  }),
  useJobDescriptionAnalysisQuery: () => ({
    data: ref(),
    error: ref(null),
    isError: ref(false),
    refetch: vi.fn(),
  }),
}))

vi.mock('@/features/cv/api/cv.queries', () => ({
  useCvVersionsPageQuery: () => ({
    data: ref(),
    isLoading: ref(false),
    isError: ref(false),
    refetch: vi.fn(),
  }),
}))

vi.mock('@/features/jd/api/jd.api', () => ({
  analyzeJobDescription: vi.fn(),
  createJobDescription: vi.fn(),
  deleteJobDescription: vi.fn(),
  updateJobDescription: vi.fn(),
}))

vi.mock('@/features/match/api/match.api', () => ({ createMatchReport: vi.fn() }))

import JdInputPage from '@/pages/jd/JdInputPage.vue'

describe('JD route regressions', () => {
  beforeEach(() => {
    routeParams.id = 'JD-A'
    mutationOptions.length = 0
    mutationStates.length = 0
    replace.mockReset()
    push.mockReset()
  })

  it('ignores a save completion after an A-to-B-to-A route cycle', async () => {
    const wrapper = mount(JdInputPage, {
      global: {
        stubs: {
          Card: { template: '<section><slot /></section>' },
          Button: { template: '<button><slot /></button>' },
        },
      },
    })
    await flushPromises()
    await wrapper.get('#jd-text').setValue('Source A')
    await wrapper.get('#company').setValue('Company A')
    await wrapper.get('#role').setValue('Engineer')
    await wrapper.get('form').trigger('submit')
    const request = mutationStates[1]?.mutate.mock.calls[0]?.[0]

    routeParams.id = 'JD-B'
    await flushPromises()
    routeParams.id = 'JD-A'
    await flushPromises()
    mutationOptions[1]?.onSuccess?.(
      { ...jobDescription, company: 'Stale callback company' } as never,
      request,
    )
    await flushPromises()

    expect(wrapper.get('#company').element.value).toBe('')
    expect(replace).not.toHaveBeenCalled()
    expect(push).not.toHaveBeenCalled()
    wrapper.unmount()
  })
})
