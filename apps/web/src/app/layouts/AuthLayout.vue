<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import { Sparkles, CheckCircle, ArrowLeft } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col lg:flex-row bg-surface">
    <!-- Left: Brand showcase panel -->
    <div
      class="hidden lg:flex lg:w-[48%] xl:w-[42%] flex-col justify-between bg-dark-bg px-12 py-12 relative overflow-hidden text-white select-none"
    >
      <!-- Ambient radial illumination -->
      <div
        class="absolute -top-24 -left-24 w-96 h-96 rounded-full opacity-30"
        style="
          background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%);
          filter: blur(40px);
        "
        aria-hidden="true"
      />
      <div
        class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full opacity-20"
        style="
          background: radial-gradient(circle, var(--color-success) 0%, transparent 70%);
          filter: blur(40px);
        "
        aria-hidden="true"
      />

      <!-- Top logo & back button -->
      <div class="relative z-10 flex items-center justify-between">
        <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shadow-brand">
            <Sparkles :size="16" class="text-white" />
          </div>
          <div class="flex items-baseline gap-1">
            <span class="font-bold text-base text-white tracking-tight">CareerFit</span>
            <span class="text-xs font-bold text-primary-light uppercase tracking-wider">CV</span>
          </div>
        </RouterLink>

        <RouterLink
          :to="ROUTES.LANDING"
          class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors"
        >
          <ArrowLeft :size="13" />
          Back to website
        </RouterLink>
      </div>

      <!-- Center: Floating Mockup Card with Glassmorphism -->
      <div class="relative z-10 my-auto py-8">
        <div
          class="glass-panel-dark p-6 rounded-2xl border border-white/10 shadow-2xl space-y-4 max-w-sm mx-auto"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-300">Live Skill Match Analysis</span>
            <span class="text-xs font-bold text-success bg-success/15 px-2 py-0.5 rounded-full"
              >+18 pts lift</span
            >
          </div>

          <div class="space-y-2">
            <div
              class="flex items-center justify-between p-2.5 rounded-lg bg-white/5 border border-white/5"
            >
              <div class="flex items-center gap-2">
                <CheckCircle :size="14" class="text-success" />
                <span class="text-xs text-slate-200 font-medium">Vue 3 Composition API</span>
              </div>
              <span class="text-xs text-slate-400">Verified</span>
            </div>

            <div
              class="flex items-center justify-between p-2.5 rounded-lg bg-white/5 border border-white/5"
            >
              <div class="flex items-center gap-2">
                <CheckCircle :size="14" class="text-success" />
                <span class="text-xs text-slate-200 font-medium"
                  >TypeScript & State Management</span
                >
              </div>
              <span class="text-xs text-slate-400">Verified</span>
            </div>
          </div>

          <div
            class="p-3 rounded-lg bg-primary/15 border border-primary/30 text-xs text-indigo-200 leading-relaxed"
          >
            "Candidate evidence grounded directly in repository projects and verified work history."
          </div>
        </div>
      </div>

      <!-- Bottom Tagline & Trust -->
      <div class="relative z-10">
        <h2 class="text-2xl font-bold text-white tracking-tight leading-snug">
          Tailor your CV for every opportunity.
        </h2>
        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
          Grounded evidence extraction, deterministic gap checking, and ATS-optimized export.
        </p>
      </div>
    </div>

    <!-- Right: Form panel -->
    <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 lg:p-16">
      <!-- Mobile header logo -->
      <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2 mb-8 lg:hidden">
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
          <Sparkles :size="16" class="text-white" />
        </div>
        <span class="font-bold text-base text-text">CareerFitCV</span>
      </RouterLink>

      <!-- Centered Form Container -->
      <div
        class="w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl border border-border shadow-auth-panel"
      >
        <slot />
      </div>
    </div>
  </div>
</template>
