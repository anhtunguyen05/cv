import { ofetch } from 'ofetch'

import { env } from '@/app/config/env'

export interface ApiErrorPayload {
  code?: string
  message?: string
  details?: Record<string, string | string[]>
}

export class ApiRequestError extends Error {
  readonly status: number
  readonly code?: string
  readonly details?: Record<string, string | string[]>
  readonly retryAfter?: number

  constructor(status: number, payload: ApiErrorPayload = {}, retryAfter?: number) {
    super(payload.message || 'Something went wrong. Please try again.')
    this.name = 'ApiRequestError'
    this.status = status
    this.code = payload.code
    this.details = payload.details
    this.retryAfter = retryAfter
  }
}

function csrfTokenFromCookie(): string | undefined {
  if (typeof document === 'undefined') return undefined
  const value = document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]

  return value ? decodeURIComponent(value) : undefined
}

export async function bootstrapCsrf(): Promise<void> {
  await ofetch('/sanctum/csrf-cookie', { credentials: 'include' })
}

export const api = ofetch.create({
  baseURL: env.apiBaseUrl,
  credentials: 'include',
  headers: {
    accept: 'application/json',
  },
  onRequest({ options }) {
    const token = csrfTokenFromCookie()
    if (token) {
      options.headers = new Headers(options.headers)
      options.headers.set('X-CSRF-TOKEN', token)
    }
  },
  async onResponseError({ response }) {
    const payload = (response._data ?? {}) as ApiErrorPayload
    throw new ApiRequestError(
      response.status,
      payload,
      Number(response.headers.get('Retry-After') ?? '') || undefined,
    )
  },
})
