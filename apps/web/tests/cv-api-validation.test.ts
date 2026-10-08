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
})
