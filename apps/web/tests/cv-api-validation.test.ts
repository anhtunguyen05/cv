import { beforeEach, describe, expect, it, vi } from 'vitest'

const api = vi.fn()
vi.mock('@/shared/api/client', () => ({ api }))

describe('CV API runtime boundary', () => {
  beforeEach(() => api.mockReset())

  it('rejects malformed profile data before it reaches query consumers', async () => {
    api.mockResolvedValue({ data: [{ id: 'profile-1', revision: 'invalid' }], meta: { per_page: 20, total: 1 } })
    const { getCvProfiles } = await import('@/features/cv/api/cv.api')

    await expect(getCvProfiles()).rejects.toThrow()
  })

  it('requests a profile summary page and parses its metadata', async () => {
    api.mockResolvedValue({
      data: [
        {
          id: 'profile-1',
          title: 'Backend CV',
          revision: 3,
          created_at: '2026-10-08T00:00:00Z',
          updated_at: '2026-10-08T00:00:00Z',
        },
      ],
      meta: { page: 2, per_page: 1, total: 3, last_page: 3 },
    })
    const { getCvProfiles } = await import('@/features/cv/api/cv.api')

    await expect(getCvProfiles(2, 1)).resolves.toMatchObject({
      page: 2,
      perPage: 1,
      total: 3,
      lastPage: 3,
      items: [{ id: 'profile-1', title: 'Backend CV', revision: 3 }],
    })
    expect(api).toHaveBeenCalledWith('/cv-profiles', { query: { page: 2, per_page: 1 } })
  })

  it('requests a version summary page without snapshot payloads', async () => {
    api.mockResolvedValue({
      data: [
        {
          id: 'version-1',
          name: 'Baseline',
          source_profile_id: 'profile-1',
          source_profile_revision: 3,
          created_at: '2026-10-08T00:00:00Z',
        },
      ],
      meta: { current_page: 2, per_page: 1, total: 2, last_page: 2 },
    })
    const { getCvVersionsPage } = await import('@/features/cv/api/cv.api')

    await expect(getCvVersionsPage(2, 1, 'profile-1')).resolves.toMatchObject({
      page: 2,
      items: [{ id: 'version-1', source_profile_revision: 3 }],
    })
    expect(api).toHaveBeenCalledWith('/cv-versions?page=2&per_page=1&profile_id=profile-1')
  })
})
