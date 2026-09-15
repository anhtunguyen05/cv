<script setup lang="ts">
import { ref } from 'vue'
import AppHeader from '@/shared/components/organisms/AppHeader.vue'
import AppSidebar from '@/shared/components/organisms/AppSidebar.vue'

const sidebarCollapsed = ref(false)
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col bg-[#f8fafc]">
    <AppHeader />

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar — hidden on mobile -->
      <div class="hidden md:flex flex-shrink-0">
        <AppSidebar v-model:collapsed="sidebarCollapsed" />
      </div>

      <!-- Main content -->
      <main class="flex-1 overflow-y-auto">
        <div class="max-w-[1440px] mx-auto px-4 md:px-6 py-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
