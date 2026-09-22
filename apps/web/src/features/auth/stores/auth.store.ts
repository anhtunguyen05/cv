import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { AuthUser } from '../types/auth.types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => !!user.value)

  function setUser(authUser: AuthUser) {
    user.value = authUser
  }

  function clearAuth() {
    user.value = null
  }

  return { user, isAuthenticated, setUser, clearAuth }
})
