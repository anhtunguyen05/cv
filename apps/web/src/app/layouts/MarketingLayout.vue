<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'

const scrolled = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 10
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div class="min-h-[100dvh] bg-[#1e293b] text-white flex flex-col">
    <!-- Nav -->
    <header
      :class="[
        'fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 h-16 transition-all duration-200',
        scrolled ? 'bg-[#1e293b]/95 backdrop-blur-sm border-b border-white/8' : 'bg-transparent',
      ]"
    >
      <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2">
        <div class="w-7 h-7 bg-[#6366f1] rounded-md flex items-center justify-center">
          <span class="text-white text-xs font-bold">CF</span>
        </div>
        <span class="font-semibold text-white">CareerFitCV</span>
      </RouterLink>

      <div class="flex items-center gap-3">
        <RouterLink
          :to="ROUTES.LOGIN"
          class="text-sm text-slate-300 hover:text-white transition-colors px-3 py-1.5"
        >
          Sign in
        </RouterLink>
        <RouterLink :to="ROUTES.REGISTER">
          <AppButton size="sm">Get started</AppButton>
        </RouterLink>
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1 pt-16">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/8 px-6 py-8 text-center text-xs text-slate-500">
      &copy; {{ new Date().getFullYear() }} CareerFitCV. Built for job seekers.
    </footer>
  </div>
</template>
