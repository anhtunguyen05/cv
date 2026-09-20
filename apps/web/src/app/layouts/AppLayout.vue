<script setup lang="ts">
import { ref } from 'vue'
import AppHeader from '@/shared/components/organisms/AppHeader.vue'
import AppSidebar from '@/shared/components/organisms/AppSidebar.vue'

const sidebarCollapsed = ref(false)
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col bg-surface">
    <AppHeader />

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar — hidden on mobile -->
      <div class="hidden md:flex flex-shrink-0 z-20">
        <AppSidebar v-model:collapsed="sidebarCollapsed" />
      </div>

      <!-- Main content area -->
      <main class="flex-1 overflow-y-auto focus:outline-none" tabindex="-1">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
