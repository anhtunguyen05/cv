import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, ref } from 'vue'
import CvEditorPage from '@/pages/cv/CvEditorPage.vue'
import { ApiRequestError } from '@/shared/api/client'

const testState = vi.hoisted(() => ({
  profile: {
    id: 'profile-1',
    title: 'Master CV',
    revision: 1,
    created_at: '2026-10-08T00:00:00Z',
    updated_at: '2026-10-08T00:00:00Z',
    personal_information: {
      full_name: 'Candidate',
      headline: null,
      email: null,
      phone: null,
      location: null,
      website_url: null,
      linkedin_url: null,
      github_url: null,
    },
    summary: null,
    skills: [],
    education: [],
    experience: [],
    projects: [],
    certificates: [],
    languages: [],
    activities: [],
  },
  updateCvTitle: vi.fn(),
  updateCvSection: vi.fn(),
  createCvVersion: vi.fn(),
  refetchedProfile: null as null | Record<string, unknown>,
  queryClient: {
    setQueryData: vi.fn(),
    invalidateQueries: vi.fn(),
  },
  router: {
    replace: vi.fn(),
    push: vi.fn(),
  },
}))

vi.mock('vue-router', () => ({
  RouterLink: { template: '<a><slot /></a>' },
  useRoute: () => ({ params: { id: 'profile-1' } }),
  useRouter: () => testState.router,
}))

vi.mock('@tanstack/vue-query', () => ({
  useQueryClient: () => testState.queryClient,
}))

vi.mock('@/features/cv/api/cv.api', () => ({
  createCvProfile: vi.fn(),
  createCvVersion: (...args: unknown[]) => testState.createCvVersion(...args),
  updateCvSection: (...args: unknown[]) => testState.updateCvSection(...args),
  updateCvTitle: (...args: unknown[]) => testState.updateCvTitle(...args),
}))

vi.mock('@/features/cv/api/cv.queries', async () => {
  return {
    useCvProfileQuery: () => ({
      data: ref(testState.profile),
      isError: ref(false),
      refetch: vi.fn(async () => ({ data: testState.refetchedProfile ?? testState.profile })),
    }),
    useCvVersionsQuery: () => ({
      data: ref([]),
      refetch: vi.fn(async () => ({ data: [] })),
    }),
  }
})

describe('CV editor regressions', () => {
  beforeEach(() => {
    testState.updateCvTitle.mockReset()
    testState.updateCvSection.mockReset()
    testState.createCvVersion.mockReset()
    testState.refetchedProfile = null
    testState.queryClient.setQueryData.mockReset()
    testState.queryClient.invalidateQueries.mockReset()
    testState.router.replace.mockReset()
    testState.router.push.mockReset()
    testState.updateCvTitle.mockResolvedValue({
      ...testState.profile,
      title: 'Updated CV',
      revision: 2,
    })
    testState.updateCvSection.mockResolvedValue({ ...testState.profile, revision: 2 })
    testState.createCvVersion.mockResolvedValue({})
  })

  it('blocks immutable Version creation while a section draft is dirty', async () => {
    const wrapper = mount(CvEditorPage)
    await flushPromises()
    await nextTick()

    const summaryButton = wrapper.findAll('button').find((button) => button.text() === 'Summary')
    await summaryButton?.trigger('click')
    await wrapper.get('textarea[aria-label="summary section"]').setValue('Unsaved summary')
    await wrapper.get('input[aria-label="Version name"]').setValue('Should not save')
    const saveVersionButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Save Version')
    await saveVersionButton?.trigger('click')

    expect(testState.createCvVersion).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain(
      'Save all Profile changes before creating an immutable Version.',
    )
  })

  it('retains the editable section when title save succeeds but section save fails', async () => {
    testState.updateCvSection.mockRejectedValueOnce(new Error('section failed'))
    const wrapper = mount(CvEditorPage)
    await flushPromises()
    await nextTick()

    await wrapper.get('input[aria-label="Profile title"]').setValue('Updated CV')
    const summaryButton = wrapper.findAll('button').find((button) => button.text() === 'Summary')
    await summaryButton?.trigger('click')
    await wrapper.get('textarea[aria-label="summary section"]').setValue('Keep this draft')
    const saveSectionButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Save section')
    await saveSectionButton?.trigger('click')
    await flushPromises()

    expect(testState.updateCvTitle).toHaveBeenCalledWith('profile-1', 'Updated CV', 1)
    expect(testState.updateCvSection).toHaveBeenCalled()
    expect(wrapper.get('textarea[aria-label="summary section"]').element.value).toBe(
      'Keep this draft',
    )
    expect(wrapper.text()).toContain('The Profile title was saved, but the section was not.')
  })

  it('preserves edits in a newly selected section while the previous section save is pending', async () => {
    let resolveSection!: (profile: typeof testState.profile) => void
    testState.updateCvSection.mockImplementationOnce(
      () =>
        new Promise((resolve) => {
          resolveSection = resolve
        }),
    )
    const wrapper = mount(CvEditorPage)
    await flushPromises()

    const summaryButton = wrapper.findAll('button').find((button) => button.text() === 'Summary')
    await summaryButton?.trigger('click')
    await wrapper.get('textarea[aria-label="summary section"]').setValue('Submitted summary')
    const saveSectionButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Save section')
    await saveSectionButton?.trigger('click')

    const projectsButton = wrapper.findAll('button').find((button) => button.text() === 'Projects')
    await projectsButton?.trigger('click')
    const projectDraft = '[{"name":"Newer local project"}]'
    await wrapper.get('textarea[aria-label="projects section"]').setValue(projectDraft)
    resolveSection({ ...testState.profile, revision: 2, summary: 'Submitted summary' })
    await flushPromises()

    expect(testState.updateCvSection).toHaveBeenCalledWith(
      'profile-1',
      'summary',
      'Submitted summary',
      1,
    )
    expect(wrapper.get('textarea[aria-label="projects section"]').element.value).toBe(projectDraft)
  })

  it('preserves a newer draft while recovering from a Version conflict', async () => {
    let rejectVersion!: (reason: unknown) => void
    testState.createCvVersion.mockImplementationOnce(
      () =>
        new Promise((_, reject) => {
          rejectVersion = reject
        }),
    )
    testState.refetchedProfile = { ...testState.profile, title: 'Remote CV', revision: 2 }
    const wrapper = mount(CvEditorPage)
    await flushPromises()

    await wrapper.get('input[aria-label="Version name"]').setValue('Conflict version')
    const saveVersionButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Save Version')
    await saveVersionButton?.trigger('click')
    await wrapper.get('input[aria-label="Profile title"]').setValue('Newer local title')
    rejectVersion(new ApiRequestError(409, { message: 'Revision conflict' }))
    await flushPromises()

    expect(wrapper.get('input[aria-label="Profile title"]').element.value).toBe(
      'Newer local title',
    )
    expect(wrapper.text()).toContain(
      'The Profile changed. Review it and try Version creation again.',
    )
  })
})
