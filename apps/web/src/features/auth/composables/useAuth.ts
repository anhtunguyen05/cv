import { storeToRefs } from 'pinia'
import { useAuthStore } from '../stores/auth.store'
import { useLoginMutation, useRegisterMutation, useLogoutMutation } from '../api/auth.mutations'

export function useAuth() {
  const authStore = useAuthStore()
  const { user, token, isAuthenticated } = storeToRefs(authStore)

  const loginMutation = useLoginMutation()
  const registerMutation = useRegisterMutation()
  const logoutMutation = useLogoutMutation()

  return {
    user,
    token,
    isAuthenticated,
    login: loginMutation.mutate,
    loginAsync: loginMutation.mutateAsync,
    isLoggingIn: loginMutation.isPending,
    loginError: loginMutation.error,
    register: registerMutation.mutate,
    registerAsync: registerMutation.mutateAsync,
    isRegistering: registerMutation.isPending,
    registerError: registerMutation.error,
    logout: logoutMutation.mutate,
    isLoggingOut: logoutMutation.isPending,
  }
}
