<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
</script>

<template>
  <div class="min-h-[100dvh] flex">
    <!-- Left: Brand panel -->
    <div class="hidden lg:flex lg:w-[45%] xl:w-[40%] flex-col bg-[#1e293b] px-10 py-12 relative overflow-hidden">
      <!-- Decorative gradient blob -->
      <div
        class="absolute top-[-100px] right-[-80px] w-[350px] h-[350px] rounded-full"
        style="background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%);"
        aria-hidden="true"
      />

      <!-- Logo -->
      <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2 relative z-10">
        <div class="w-8 h-8 bg-[#6366f1] rounded-md flex items-center justify-center">
          <span class="text-white text-sm font-bold">CF</span>
        </div>
        <span class="font-semibold text-white text-lg">CareerFitCV</span>
      </RouterLink>

      <!-- Brand tagline -->
      <div class="mt-auto relative z-10">
        <p class="text-3xl font-bold text-white leading-snug tracking-tight">
          Your CV.<br />
          Tailored precisely.
        </p>
        <p class="mt-3 text-slate-400 text-sm leading-relaxed max-w-[30ch]">
          Match your experience to each job description and export a polished CV in minutes.
        </p>

        <!-- Decorative mockup lines -->
        <div class="mt-10 space-y-2">
          <div class="flex items-center gap-2 text-xs text-slate-500">
            <div class="w-2 h-2 rounded-full bg-[#10b981]" />
            <span>TypeScript — matched</span>
          </div>
          <div class="flex items-center gap-2 text-xs text-slate-500">
            <div class="w-2 h-2 rounded-full bg-[#f59e0b]" />
            <span>REST API — weak evidence</span>
          </div>
          <div class="flex items-center gap-2 text-xs text-slate-500">
            <div class="w-2 h-2 rounded-full bg-[#ef4444]" />
            <span>Redux — missing</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Form area -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-10 bg-[#f8fafc]">
      <!-- Mobile logo -->
      <RouterLink :to="ROUTES.LANDING" class="flex items-center gap-2 mb-8 lg:hidden">
        <div class="w-7 h-7 bg-[#6366f1] rounded-md flex items-center justify-center">
          <span class="text-white text-xs font-bold">CF</span>
        </div>
        <span class="font-semibold text-[#0f172a]">CareerFitCV</span>
      </RouterLink>

      <div class="w-full max-w-sm">
        <slot />
      </div>
    </div>
  </div>
</template>
