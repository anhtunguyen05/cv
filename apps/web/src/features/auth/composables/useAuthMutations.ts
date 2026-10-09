import { useMutation } from '@tanstack/vue-query'
import { getMe, login, register, logout } from '../api/auth.api'
import { useAuthStore } from '../stores/auth.store'
import { useRouter } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import type { LoginCredentials, RegisterCredentials } from '../types/auth.types'
import { ApiRequestError } from '@/shared/api/client'
import { queryClient } from '@/app/providers/vue-query'

function safeReturnTo(value: unknown): string | null {
  if (
    typeof value !== 'string' ||
    !value.startsWith('/') ||
    value.startsWith('//') ||
    value.includes('\\')
  ) {
    return null
  }
  return value
}

export function useLoginMutation() {
  const authStore = useAuthStore()
  const router = useRouter()

  return useMutation({
    mutationFn: (credentials: LoginCredentials) => login(credentials),
    onSuccess: (data) => {
      authStore.setUser(data.data.user)
      const returnTo = safeReturnTo(router.currentRoute.value.query.return_to)
      void router.push(returnTo ?? ROUTES.DASHBOARD)
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
      if (
        error instanceof ApiRequestError &&
        [400, 401, 403, 409, 419, 422, 429].includes(error.status)
      ) {
        return
      }
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
      void queryClient.cancelQueries().finally(() => {
        queryClient.clear()
        void router.push(ROUTES.LOGIN)
      })
    },
  })
}
