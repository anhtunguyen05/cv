import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { AuthUser } from '../types/auth.types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const token = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  function setAuth(authUser: AuthUser, authToken: string) {
    user.value = authUser
    token.value = authToken
  }

  function clearAuth() {
    user.value = null
    token.value = null
  }

  return { user, token, isAuthenticated, setAuth, clearAuth }
})
