import { api } from '@/shared/api/client'
import { bootstrapCsrf, ApiRequestError } from '@/shared/api/client'
import type {
  AuthResponse,
  LoginCredentials,
  RegisterCredentials,
  AuthUser,
} from '../types/auth.types'
import { authResponseSchema } from '../schemas/auth.schema'

async function parseAuthResponse(request: Promise<unknown>): Promise<AuthResponse> {
  return authResponseSchema.parse(await request)
}

export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  await bootstrapCsrf()
  try {
    return await parseAuthResponse(api<unknown>('/auth/login', { method: 'POST', body: credentials }))
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 419) {
      await bootstrapCsrf()
      return parseAuthResponse(api<unknown>('/auth/login', { method: 'POST', body: credentials }))
    }
    throw error
  }
}

export function register(credentials: RegisterCredentials): Promise<AuthResponse> {
  return registerWithCsrfRecovery(credentials)
}

async function registerWithCsrfRecovery(credentials: RegisterCredentials): Promise<AuthResponse> {
  await bootstrapCsrf()
  try {
    return await parseAuthResponse(
      api<unknown>('/auth/register', { method: 'POST', body: credentials }),
    )
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 419) {
      await bootstrapCsrf()
      return parseAuthResponse(api<unknown>('/auth/register', { method: 'POST', body: credentials }))
    }
    throw error
  }
}

export function logout(): Promise<void> {
  return api<void>('/auth/logout', { method: 'POST' })
}

export async function getMe(): Promise<AuthUser> {
  const response = authResponseSchema.parse(await api<unknown>('/auth/me'))
  return response.data.user
}
