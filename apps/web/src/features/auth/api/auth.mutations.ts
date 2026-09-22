import { useMutation } from '@tanstack/vue-query'
import { getMe, login, register, logout } from './auth.api'
import { useAuthStore } from '../stores/auth.store'
import { useRouter } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import type { LoginCredentials, RegisterCredentials } from '../types/auth.types'
import { ApiRequestError } from '@/shared/api/client'

export function useLoginMutation() {
  const authStore = useAuthStore()
  const router = useRouter()

  return useMutation({
    mutationFn: (credentials: LoginCredentials) => login(credentials),
    onSuccess: (data) => {
      authStore.setUser(data.data.user)
      router.push(ROUTES.DASHBOARD)
    },
  })
}

export function useRegisterMutation() {
  const authStore = useAuthStore()
  const router = useRouter()

  return useMutation({
    mutationFn: (credentials: RegisterCredentials) => register(credentials),
    onSuccess: (data) => {
      authStore.setUser(data.data.user)
      router.push(ROUTES.DASHBOARD)
    },
    onError: async (error) => {
      // A network failure leaves the registration outcome unknown. Reconcile once
      // before allowing the user to submit again, avoiding duplicate accounts.
      if (error instanceof ApiRequestError) return
      try {
        authStore.setUser(await getMe())
        await router.push(ROUTES.DASHBOARD)
      } catch {
        // The form keeps the original error and remains retryable.
      }
    },
  })
}

export function useLogoutMutation() {
  const authStore = useAuthStore()
  const router = useRouter()

  return useMutation({
    mutationFn: logout,
    onSuccess: () => {
      authStore.clearAuth()
      router.push(ROUTES.LOGIN)
    },
  })
}
