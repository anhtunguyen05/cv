import type { z } from 'zod'
import type { authResponseSchema, authUserSchema } from '../schemas/auth.schema'

export type AuthUser = z.infer<typeof authUserSchema>

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterCredentials {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type AuthResponse = z.infer<typeof authResponseSchema>
