import { z } from 'zod'

export const emailSchema = z
  .string({ required_error: 'Email is required' })
  .email('Please enter a valid email address')

export const passwordSchema = z
  .string({ required_error: 'Password is required' })
  .min(12, 'Password must be at least 12 characters')

export const requiredString = (fieldName: string) =>
  z.string({ required_error: `${fieldName} is required` }).min(1, `${fieldName} is required`)

export const optionalString = z.string().optional()
