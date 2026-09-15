<script setup lang="ts">
import type { Component } from 'vue'
import AppButton from '../atoms/AppButton.vue'

interface Props {
  icon?: Component
  title: string
  description?: string
  actionLabel?: string
}

withDefaults(defineProps<Props>(), {
  description: '',
  actionLabel: '',
})

const emit = defineEmits<{ action: [] }>()
</script>

<template>
  <div class="flex flex-col items-center justify-center text-center py-16 px-6 gap-4">
    <div
      v-if="icon"
      class="w-12 h-12 rounded-xl bg-[#f1f5f9] flex items-center justify-center text-[#64748b]"
    >
      <component :is="icon" :size="22" :stroke-width="1.5" />
    </div>

    <div class="max-w-sm">
      <h3 class="text-base font-semibold text-[#0f172a]">{{ title }}</h3>
      <p v-if="description" class="mt-1 text-sm text-[#64748b]">{{ description }}</p>
    </div>

    <AppButton
      v-if="actionLabel"
      size="sm"
      @click="emit('action')"
    >
      {{ actionLabel }}
    </AppButton>

    <slot />
  </div>
</template>
