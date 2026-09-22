<script setup lang="ts">
import { ref } from 'vue'
import {
  Menu,
  X,
  User,
  LogOut,
  Settings,
  ChevronDown,
  Sparkles,
  LayoutDashboard,
  LayoutTemplate,
  FileText,
  Briefcase,
} from 'lucide-vue-next'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const router = useRouter()
const route = useRoute()

const navLinks = [
  { label: 'Dashboard', to: ROUTES.DASHBOARD, icon: LayoutDashboard },
  { label: 'CV Editor', to: ROUTES.CV_EDIT('new'), icon: FileText },
  { label: 'Job Analysis', to: ROUTES.JD_NEW, icon: Briefcase },
  { label: 'Templates', to: ROUTES.TEMPLATES, icon: LayoutTemplate },
]

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function logout() {
  userMenuOpen.value = false
  router.push(ROUTES.LOGIN)
}
</script>

<template>
  <header
    class="h-16 border-b border-border/80 bg-white/95 backdrop-blur-md flex items-center px-4 sm:px-6 lg:px-8 gap-4 sm:gap-6 sticky top-0 z-40 transition-colors"
  >
    <!-- Logo -->
    <RouterLink
      :to="ROUTES.DASHBOARD"
      class="flex items-center gap-2.5 flex-shrink-0 group focus:outline-none"
    >
      <div
        class="w-8 h-8 bg-primary rounded-xl flex items-center justify-center shadow-brand-icon group-hover:bg-primary-hover transition-colors"
      >
        <Sparkles :size="16" :stroke-width="2" class="text-white" />
      </div>
      <div class="flex items-baseline gap-1">
        <span class="font-bold text-base text-text tracking-tight">CareerFit</span>
        <span class="text-xs font-bold text-primary tracking-wider uppercase">CV</span>
      </div>
    </RouterLink>

    <!-- Desktop Nav Links -->
    <nav class="hidden md:flex items-center gap-1.5 ml-2 flex-1">
      <RouterLink
        v-for="link in navLinks"
        :key="link.to"
        :to="link.to"
        :class="[
          'px-3 py-1.5 text-xs sm:text-[13px] font-medium rounded-lg transition-all duration-150 flex items-center gap-2',
          route.path === link.to || (link.to !== ROUTES.DASHBOARD && route.path.startsWith(link.to))
            ? 'bg-primary-muted text-primary-dark font-semibold shadow-2xs'
            : 'text-text-muted hover:text-text hover:bg-surface',
        ]"
      >
        <component :is="link.icon" :size="15" :stroke-width="1.75" />
        {{ link.label }}
      </RouterLink>
    </nav>

    <div class="flex-1 md:flex-none" />

    <!-- Right Controls -->
    <div class="flex items-center gap-3">
      <!-- Status Badge -->
      <div
        class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface border border-border text-xs font-medium text-text-muted"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-success" />
        <span>ATS Engine Ready</span>
      </div>

      <!-- User profile button & dropdown -->
      <div class="relative">
        <button
          class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-lg text-xs sm:text-sm font-medium text-text-secondary border border-transparent hover:border-border hover:bg-surface transition-all cursor-pointer select-none"
          :aria-expanded="userMenuOpen"
          @click="userMenuOpen = !userMenuOpen"
        >
          <div
            class="w-7 h-7 rounded-full bg-primary-muted border border-primary-border/60 flex items-center justify-center text-primary font-semibold text-xs"
          >
            TU
          </div>
          <span class="hidden sm:inline font-semibold text-text">Nguyen Anh Tu</span>
          <ChevronDown :size="14" :stroke-width="1.5" class="text-text-subtle" />
        </button>

        <!-- Dropdown menu -->
        <div
          v-if="userMenuOpen"
          class="absolute right-0 top-full mt-1.5 w-52 bg-white border border-border rounded-xl shadow-dropdown py-1.5 z-50 animate-in fade-in slide-in-from-top-1 duration-150"
        >
          <div class="px-3.5 py-2 border-b border-surface-muted">
            <p class="text-xs font-semibold text-text">Nguyen Anh Tu</p>
            <p class="text-xs text-text-muted truncate">tu@example.com</p>
          </div>

          <div class="py-1">
            <RouterLink
              to="/dashboard"
              class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-text-quiet hover:bg-surface hover:text-text transition-colors"
              @click="userMenuOpen = false"
            >
              <User :size="14" :stroke-width="1.5" />
              Profile & Account
            </RouterLink>
            <RouterLink
              to="/templates"
              class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-text-quiet hover:bg-surface hover:text-text transition-colors"
              @click="userMenuOpen = false"
            >
              <Settings :size="14" :stroke-width="1.5" />
              Preferences
            </RouterLink>
          </div>

          <div class="pt-1 border-t border-surface-muted">
            <button
              class="flex w-full items-center gap-2 px-3.5 py-1.5 text-xs text-danger hover:bg-danger-muted transition-colors cursor-pointer"
              @click="logout"
            >
              <LogOut :size="14" :stroke-width="1.5" />
              Sign out
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile hamburger toggle -->
      <button
        class="md:hidden p-1.5 rounded-lg text-text-muted hover:bg-surface hover:text-text transition-colors"
        :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
        @click="toggleMobileMenu"
      >
        <X v-if="mobileMenuOpen" :size="18" :stroke-width="1.5" />
        <Menu v-else :size="18" :stroke-width="1.5" />
      </button>
    </div>
  </header>

  <!-- Mobile nav drawer -->
  <div
    v-if="mobileMenuOpen"
    class="md:hidden border-b border-border bg-white px-4 py-3 space-y-1 shadow-md z-30"
  >
    <RouterLink
      v-for="link in navLinks"
      :key="link.to"
      :to="link.to"
      class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
      :class="
        route.path === link.to
          ? 'bg-primary-muted text-primary-dark'
          : 'text-text-muted hover:bg-surface hover:text-text'
      "
      @click="mobileMenuOpen = false"
    >
      <component :is="link.icon" :size="16" :stroke-width="1.5" />
      {{ link.label }}
    </RouterLink>
  </div>
</template>
