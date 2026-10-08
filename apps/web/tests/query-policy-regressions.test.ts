import { ref, toValue } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const useQuery = vi.fn((options: unknown) => options)

vi.mock('@tanstack/vue-query', () => ({ useQuery }))
vi.mock('@/features/cv/api/cv.api', () => ({
  getCvPreview: vi.fn(),
  getCvProfiles: vi.fn(),
  getCvProfile: vi.fn(),
  getCvVersionsPage: vi.fn(),
}))
vi.mock('@/features/templates/api/templates.api', () => ({ getTemplates: vi.fn() }))

describe('query policy regressions', () => {
  beforeEach(() => useQuery.mockClear())

  it('keeps paged CV version summaries fresh for the immutable resource lifetime', async () => {
    const { useCvVersionsPageQuery } = await import('@/features/cv/api/cv.queries')

    useCvVersionsPageQuery(ref(1))

    expect(useQuery.mock.calls[0]?.[0]).toMatchObject({
      staleTime: 10 * 60 * 1000,
      gcTime: 10 * 60 * 1000,
    })
  })

  it('does not fetch templates while the version-selection route is active', async () => {
    const { useTemplatesQuery } = await import('@/features/templates/api/templates.queries')

    useTemplatesQuery(false)
    const disabledOptions = useQuery.mock.calls[0]?.[0] as { enabled: unknown }
    expect(toValue(disabledOptions.enabled)).toBe(false)

    useTemplatesQuery(true)
    const enabledOptions = useQuery.mock.calls[1]?.[0] as { enabled: unknown }
    expect(toValue(enabledOptions.enabled)).toBe(true)
  })
})
