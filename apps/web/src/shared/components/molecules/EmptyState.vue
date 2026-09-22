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
  <div class="flex flex-col items-center justify-center text-center py-12 px-6">
    <!-- Concentric icon wrapper -->
    <div v-if="icon" class="relative mb-5 flex items-center justify-center">
      <div
        class="w-16 h-16 rounded-2xl bg-primary-muted border border-primary-border/60 flex items-center justify-center text-primary shadow-empty-state"
      >
        <component :is="icon" :size="28" :stroke-width="1.5" />
      </div>
    </div>

    <div class="max-w-md">
      <h3 class="text-base font-semibold text-text tracking-tight">{{ title }}</h3>
      <p v-if="description" class="mt-1.5 text-sm text-text-muted leading-relaxed">
        {{ description }}
      </p>
    </div>

    <div v-if="actionLabel || $slots.action" class="mt-6 flex items-center gap-3">
      <AppButton v-if="actionLabel" size="sm" @click="emit('action')">
        {{ actionLabel }}
      </AppButton>
      <slot name="action" />
    </div>

    <slot />
  </div>
</template>
