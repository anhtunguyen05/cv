<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Printer } from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'

function printCV() {
  window.print()
}
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col bg-[#f1f5f9]">
    <!-- Minimal top bar -->
    <header class="no-print h-12 bg-white border-b border-[#e2e8f0] flex items-center justify-between px-4 lg:px-6 flex-shrink-0 z-10">
      <RouterLink :to="ROUTES.DASHBOARD" class="flex items-center gap-2">
        <div class="w-6 h-6 bg-[#6366f1] rounded-md flex items-center justify-center">
          <span class="text-white text-[10px] font-bold">CF</span>
        </div>
        <span class="text-sm font-medium text-[#64748b]">Preview</span>
      </RouterLink>

      <div class="flex items-center gap-2">
        <slot name="controls" />
        <AppButton size="sm" variant="outline" @click="printCV">
          <Printer :size="14" :stroke-width="1.5" />
          Export / Print
        </AppButton>
      </div>
    </header>

    <!-- Canvas: neutral background, document centered -->
    <main class="flex-1 overflow-y-auto py-8 px-4 flex justify-center">
      <slot />
    </main>
  </div>
</template>
