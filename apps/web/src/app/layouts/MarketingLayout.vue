<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { Sparkles, ArrowRight } from 'lucide-vue-next'

const scrolled = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 15
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div
    class="min-h-[100dvh] bg-dark-bg text-white flex flex-col selection:bg-primary selection:text-white"
  >
    <!-- Ambient top glow -->
    <div
      class="pointer-events-none fixed top-0 inset-x-0 h-[500px] ambient-glow z-0"
      aria-hidden="true"
    />

    <!-- Header / Navbar -->
    <header
      :class="[
        'fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 lg:px-12 h-16 transition-all duration-300',
        scrolled
          ? 'bg-dark-bg/85 backdrop-blur-md border-b border-white/10 shadow-dark-nav'
          : 'bg-transparent border-b border-white/5',
      ]"
    >
      <!-- Brand Logo -->
      <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2.5 group">
        <div
          class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shadow-brand group-hover:bg-primary-hover transition-colors"
        >
          <Sparkles :size="16" class="text-white" />
        </div>
        <div class="flex items-baseline gap-1">
          <span class="font-bold text-base text-white tracking-tight">CareerFit</span>
          <span class="text-xs font-bold text-primary-light uppercase tracking-wider">CV</span>
        </div>
      </RouterLink>

      <!-- Center navigation links -->
      <nav class="hidden md:flex items-center gap-8 text-xs font-medium text-slate-300">
        <a href="#how-it-works" class="hover:text-white transition-colors">How it works</a>
        <a href="#features" class="hover:text-white transition-colors">Features</a>
        <a href="#metrics" class="hover:text-white transition-colors">Evidence Analysis</a>
        <RouterLink :to="ROUTES.TEMPLATES" class="hover:text-white transition-colors"
          >Templates</RouterLink
        >
      </nav>

      <!-- Right Auth Actions -->
      <div class="flex items-center gap-3">
        <RouterLink
          :to="ROUTES.LOGIN"
          class="text-xs font-medium text-slate-300 hover:text-white transition-colors px-3 py-1.5"
        >
          Sign in
        </RouterLink>
        <RouterLink :to="ROUTES.REGISTER">
          <AppButton size="sm" class="shadow-brand-soft">
            Get started
            <ArrowRight :size="13" :stroke-width="2" />
          </AppButton>
        </RouterLink>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 pt-16 relative z-10">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/10 bg-dark-deep px-6 lg:px-16 py-12">
      <div
        class="max-w-[1440px] mx-auto flex flex-col md:flex-row items-center justify-between gap-6"
      >
        <div class="flex items-center gap-2.5">
          <div class="w-6 h-6 bg-primary rounded-md flex items-center justify-center">
            <Sparkles :size="13" class="text-white" />
          </div>
          <span class="font-bold text-sm text-white">CareerFitCV</span>
          <span class="text-xs text-slate-500 ml-2"
            >© {{ new Date().getFullYear() }} All rights reserved.</span
          >
        </div>

        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-400">
          <RouterLink :to="ROUTES.LANDING" class="hover:text-white transition-colors"
            >Product</RouterLink
          >
          <RouterLink :to="ROUTES.TEMPLATES" class="hover:text-white transition-colors"
            >CV Templates</RouterLink
          >
          <RouterLink :to="ROUTES.LOGIN" class="hover:text-white transition-colors"
            >Sign in</RouterLink
          >
          <RouterLink :to="ROUTES.REGISTER" class="hover:text-white transition-colors"
            >Create account</RouterLink
          >
        </div>
      </div>
    </footer>
  </div>
</template>
