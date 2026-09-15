<script setup lang="ts">
import { ref } from 'vue'
import { Menu, X, User, LogOut, Settings, ChevronDown } from 'lucide-vue-next'
import { RouterLink, useRouter } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const router = useRouter()

const navLinks = [
  { label: 'Dashboard', to: ROUTES.DASHBOARD },
  { label: 'Templates', to: ROUTES.TEMPLATES },
]

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function logout() {
  // Auth store logout will be wired when auth feature is ready
  router.push(ROUTES.LOGIN)
}
</script>

<template>
  <header class="h-14 border-b border-[#e2e8f0] bg-white flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-40">
    <!-- Logo -->
    <RouterLink :to="ROUTES.DASHBOARD" class="flex items-center gap-2 flex-shrink-0">
      <div class="w-7 h-7 bg-[#6366f1] rounded-md flex items-center justify-center">
        <span class="text-white text-xs font-bold tracking-tight">CF</span>
      </div>
      <span class="font-semibold text-sm text-[#0f172a] hidden sm:block">CareerFitCV</span>
    </RouterLink>

    <!-- Desktop Nav -->
    <nav class="hidden md:flex items-center gap-1 flex-1">
      <RouterLink
        v-for="link in navLinks"
        :key="link.to"
        :to="link.to"
        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-150"
        :class="$route.path === link.to
          ? 'bg-[#eef2ff] text-[#4338ca]'
          : 'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f8fafc]'"
      >
        {{ link.label }}
      </RouterLink>
    </nav>

    <div class="flex-1 md:flex-none" />

    <!-- User dropdown -->
    <div class="relative">
      <button
        class="flex items-center gap-1.5 px-2 py-1.5 rounded-md text-sm text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] transition-colors"
        @click="userMenuOpen = !userMenuOpen"
      >
        <div class="w-6 h-6 rounded-full bg-[#eef2ff] flex items-center justify-center">
          <User :size="14" :stroke-width="1.5" class="text-[#6366f1]" />
        </div>
        <ChevronDown :size="14" :stroke-width="1.5" />
      </button>

      <div
        v-if="userMenuOpen"
        class="absolute right-0 top-full mt-1 w-44 bg-white border border-[#e2e8f0] rounded-lg shadow-lg py-1 z-50"
      >
        <RouterLink
          :to="'/settings'"
          class="flex items-center gap-2 px-3 py-2 text-sm text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] transition-colors"
          @click="userMenuOpen = false"
        >
          <Settings :size="14" :stroke-width="1.5" />
          Settings
        </RouterLink>
        <button
          class="flex w-full items-center gap-2 px-3 py-2 text-sm text-[#ef4444] hover:bg-[#fef2f2] transition-colors"
          @click="logout"
        >
          <LogOut :size="14" :stroke-width="1.5" />
          Sign out
        </button>
      </div>
    </div>

    <!-- Mobile hamburger -->
    <button
      class="md:hidden p-1.5 rounded-md text-[#64748b] hover:bg-[#f8fafc] transition-colors"
      :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
      @click="toggleMobileMenu"
    >
      <X v-if="mobileMenuOpen" :size="18" :stroke-width="1.5" />
      <Menu v-else :size="18" :stroke-width="1.5" />
    </button>
  </header>

  <!-- Mobile nav drawer -->
  <div
    v-if="mobileMenuOpen"
    class="md:hidden border-b border-[#e2e8f0] bg-white px-4 py-3 space-y-1"
  >
    <RouterLink
      v-for="link in navLinks"
      :key="link.to"
      :to="link.to"
      class="block px-3 py-2 rounded-md text-sm font-medium transition-colors"
      :class="$route.path === link.to
        ? 'bg-[#eef2ff] text-[#4338ca]'
        : 'text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a]'"
      @click="mobileMenuOpen = false"
    >
      {{ link.label }}
    </RouterLink>
  </div>
</template>
