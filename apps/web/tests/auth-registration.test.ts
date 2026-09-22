import { beforeEach, describe, expect, it, vi } from 'vitest'

const api = vi.fn()
const bootstrapCsrf = vi.fn()

vi.mock('@/shared/api/client', () => {
  class ApiRequestError extends Error {
    constructor(
      readonly status: number,
      readonly payload: { code?: string; details?: Record<string, string> } = {},
      readonly retryAfter?: number,
    ) {
      super(payload.code ?? 'Request failed')
    }

    get details() {
      return this.payload.details
    }
  }

  return { api, bootstrapCsrf, ApiRequestError }
})

const credentials = {
  name: 'Nguyen Anh Tu',
  email: 'tu@example.com',
  password: 'a-secure-password',
  password_confirmation: 'a-secure-password',
}

describe('registration transport', () => {
  beforeEach(() => {
    api.mockReset()
    bootstrapCsrf.mockReset().mockResolvedValue(undefined)
  })

  it('bootstraps CSRF and returns the public user on success', async () => {
    api.mockResolvedValue({ data: { user: { id: '1', name: credentials.name, email: credentials.email } } })
    const { register } = await import('@/features/auth/api/auth.api')

    await expect(register(credentials)).resolves.toEqual(expect.objectContaining({ data: expect.any(Object) }))
    expect(bootstrapCsrf).toHaveBeenCalledTimes(1)
    expect(api).toHaveBeenCalledWith('/auth/register', { method: 'POST', body: credentials })
  })

  it('refreshes CSRF and retries once after an expired session', async () => {
    const { ApiRequestError } = await import('@/shared/api/client')
    api.mockRejectedValueOnce(new ApiRequestError(419)).mockResolvedValueOnce({ data: { user: {} } })
    const { register } = await import('@/features/auth/api/auth.api')

    await register(credentials)
    expect(bootstrapCsrf).toHaveBeenCalledTimes(2)
    expect(api).toHaveBeenCalledTimes(2)
  })

  it('preserves duplicate-email and throttling errors for the form layer', async () => {
    const { ApiRequestError } = await import('@/shared/api/client')
    api.mockRejectedValue(new ApiRequestError(429, { code: 'THROTTLED', details: { email: 'Try later' } }, 30))
    const { register } = await import('@/features/auth/api/auth.api')

    await expect(register(credentials)).rejects.toMatchObject({ status: 429, retryAfter: 30 })
  })

  it('exposes current-account lookup for lost-response reconciliation', async () => {
    api.mockResolvedValue({ data: { user: { id: '1', name: credentials.name, email: credentials.email } } })
    const { getMe } = await import('@/features/auth/api/auth.api')

    await expect(getMe()).resolves.toEqual({ id: '1', name: credentials.name, email: credentials.email })
    expect(api).toHaveBeenCalledWith('/auth/me')
  })
})
