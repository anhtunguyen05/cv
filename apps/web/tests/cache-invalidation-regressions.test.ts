import { beforeEach, describe, expect, it, vi } from 'vitest'
import { cvQueryKeys } from '@/features/cv/api/cv.keys'
import { dashboardQueryKeys } from '@/features/dashboard/api/dashboard.keys'

const queryClient = {
  setQueryData: vi.fn(),
  invalidateQueries: vi.fn(() => Promise.resolve()),
}

vi.mock('@tanstack/vue-query', () => ({ useQueryClient: () => queryClient }))
vi.mock('@/features/cv/api/cv.api', () => ({
  createCvProfile: vi.fn(),
  createCvVersion: vi.fn(),
  updateCvSection: vi.fn(),
  updateCvTitle: vi.fn(),
}))
vi.mock('@/features/cv/api/cv.queries', () => ({
  useCvProfileQuery: () => ({}),
  useCvVersionsQuery: () => ({}),
}))

describe('cache invalidation regressions', () => {
  beforeEach(() => {
    queryClient.setQueryData.mockReset()
    queryClient.invalidateQueries.mockReset()
    queryClient.invalidateQueries.mockImplementation(() => Promise.resolve())
  })

  it('invalidates profile list pages without refetching the active detail cache', async () => {
    const { useCvEditorOperations } = await import(
      '@/features/cv/composables/useCvEditorOperations'
    )

    await useCvEditorOperations('profile-1', 1).invalidateProfiles()

    expect(queryClient.invalidateQueries).toHaveBeenNthCalledWith(1, {
      queryKey: cvQueryKeys.profilesList(),
    })
    expect(queryClient.invalidateQueries).toHaveBeenNthCalledWith(2, {
      queryKey: dashboardQueryKeys.summary(),
    })
    expect(queryClient.invalidateQueries).not.toHaveBeenCalledWith({
      queryKey: cvQueryKeys.profile('profile-1'),
    })
  })
})
