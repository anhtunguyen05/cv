import { describe, expect, it } from 'vitest'
import { registerSchema } from '@/features/auth/schemas/auth.schema'

describe('registration validation', () => {
  it('requires the backend password length and confirmation', () => {
    const result = registerSchema.safeParse({
      name: 'Tu',
      email: 'tu@example.com',
      password: 'short',
      password_confirmation: 'short',
    })

    expect(result.success).toBe(false)
    if (!result.success) expect(result.error.flatten().fieldErrors.password).toContain('Password must be at least 12 characters')
  })
})
