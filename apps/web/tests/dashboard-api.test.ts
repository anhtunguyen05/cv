import { beforeEach, describe, expect, it, vi } from 'vitest'

const api = vi.fn()
vi.mock('@/shared/api/client', () => ({ api }))

describe('dashboard summary API boundary', () => {
  beforeEach(() => api.mockReset())

  it('parses count-only responses without report objects', async () => {
    api.mockResolvedValue({
      data: { cv_profiles: 12, job_descriptions: 28, match_reports: 42 },
    })
    const { getDashboardSummary } = await import('@/features/dashboard/api/dashboard.api')

    await expect(getDashboardSummary()).resolves.toEqual({
      cv_profiles: 12,
      job_descriptions: 28,
      match_reports: 42,
    })
    expect(api).toHaveBeenCalledWith('/dashboard/summary')
  })

  it('rejects malformed count responses at the API boundary', async () => {
    api.mockResolvedValue({ data: { cv_profiles: '12' } })
    const { getDashboardSummary } = await import('@/features/dashboard/api/dashboard.api')

    await expect(getDashboardSummary()).rejects.toThrow()
  })
})
