import { useMutation } from '@tanstack/vue-query'
import { login, register, logout } from './auth.api'
import { useAuthStore } from '../stores/auth.store'
import { useRouter } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import type { LoginCredentials, RegisterCredentials } from '../types/auth.types'

export function useLoginMutation() {
  const authStore = useAuthStore()
  const router = useRouter()

  return useMutation({
    mutationFn: (credentials: LoginCredentials) => login(credentials),
    onSuccess: (data) => {
      authStore.setAuth(data.user, data.token)
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
      authStore.setAuth(data.user, data.token)
      router.push(ROUTES.DASHBOARD)
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
