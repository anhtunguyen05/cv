import { api } from '@/shared/api/client'
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
  return api<AuthResponse>('/auth/register', { method: 'POST', body: credentials })
}

export function logout(): Promise<void> {
  return api<void>('/auth/logout', { method: 'POST' })
}

export function getMe(): Promise<AuthUser> {
  return api<AuthUser>('/auth/me')
}
