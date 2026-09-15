import { z } from 'zod'
import { emailSchema, passwordSchema, requiredString } from '@/shared/schemas/common.schema'

export const loginSchema = z.object({
  email: emailSchema,
  password: z.string({ required_error: 'Password is required' }).min(1, 'Password is required'),
})

export const registerSchema = z
  .object({
    name: requiredString('Full name'),
    email: emailSchema,
    password: passwordSchema,
    password_confirmation: z.string({ required_error: 'Please confirm your password' }),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

export type LoginFormData = z.infer<typeof loginSchema>
export type RegisterFormData = z.infer<typeof registerSchema>
