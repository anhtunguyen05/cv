import { api } from '@/shared/api/client'
import { bootstrapCsrf, ApiRequestError } from '@/shared/api/client'
import type {
  AuthResponse,
  LoginCredentials,
  RegisterCredentials,
  AuthUser,
} from '../types/auth.types'

export function login(credentials: LoginCredentials): Promise<AuthResponse> {
  return api<AuthResponse>('/auth/login', { method: 'POST', body: credentials })
}

export function register(credentials: RegisterCredentials): Promise<AuthResponse> {
  return registerWithCsrfRecovery(credentials)
}

async function registerWithCsrfRecovery(credentials: RegisterCredentials): Promise<AuthResponse> {
  await bootstrapCsrf()
  try {
    return await api<AuthResponse>('/auth/register', { method: 'POST', body: credentials })
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 419) {
      await bootstrapCsrf()
      return api<AuthResponse>('/auth/register', { method: 'POST', body: credentials })
    }
    throw error
  }
}

export function logout(): Promise<void> {
  return api<void>('/auth/logout', { method: 'POST' })
}

export async function getMe(): Promise<AuthUser> {
  const response = await api<AuthResponse>('/auth/me')
  return response.data.user
}
