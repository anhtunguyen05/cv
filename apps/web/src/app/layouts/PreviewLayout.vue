<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Printer, ArrowLeft, Sparkles, Download } from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'

function printCV() {
  window.print()
}
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col bg-surface-muted">
    <!-- Document toolbar -->
    <header class="no-print h-16 bg-white/95 backdrop-blur-md border-b border-border flex items-center justify-between px-6 lg:px-10 flex-shrink-0 sticky top-0 z-30 shadow-xs">
      <div class="flex items-center gap-4">
        <RouterLink
          :to="ROUTES.DASHBOARD"
          class="inline-flex items-center gap-2 text-sm font-medium text-text-muted hover:text-text transition-colors py-1.5 px-2.5 rounded-lg hover:bg-surface"
        >
          <ArrowLeft :size="16" />
          <span class="hidden sm:inline">Dashboard</span>
        </RouterLink>

        <div class="h-5 w-px bg-border hidden sm:block" />

        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 bg-primary rounded-lg flex items-center justify-center shadow-2xs">
            <Sparkles :size="14" class="text-white" />
          </div>
          <span class="text-sm font-bold text-text">CV Document Preview</span>
          <span class="hidden md:inline text-xs font-mono text-success-hover bg-success-muted border border-success-border px-2.5 py-0.5 rounded-full font-medium">
            A4 Standard · 1 Page
          </span>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="flex items-center gap-3">
        <slot name="controls" />

        <AppButton size="md" variant="outline" @click="printCV">
          <Printer :size="15" :stroke-width="1.5" />
          <span class="hidden sm:inline">Print / PDF</span>
        </AppButton>

        <AppButton size="md" @click="printCV">
          <Download :size="15" :stroke-width="2" />
          <span>Export</span>
        </AppButton>
      </div>
    </header>

    <!-- Canvas area -->
    <main class="flex-1 overflow-y-auto py-8 sm:py-12 px-4 flex justify-center bg-surface-muted">
      <slot />
    </main>
  </div>
</template>
