<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  status: 'active' | 'processing' | 'inactive' | 'error'
  label?: string
}

const props = defineProps<Props>()

const dotColor = computed(() => {
  const map = {
    active: 'bg-success',
    processing: 'bg-primary',
    inactive: 'bg-text-subtle',
    error: 'bg-danger',
  }
  return map[props.status]
})

const pingColor = computed(() => {
  const map = {
    active: 'bg-success/40',
    processing: 'bg-primary/40',
    inactive: 'transparent',
    error: 'bg-danger/40',
  }
  return map[props.status]
})
</script>

<template>
  <span class="inline-flex items-center gap-2 text-xs font-medium text-text-muted">
    <span class="relative flex h-2 w-2">
      <span
        v-if="status === 'active' || status === 'processing'"
        :class="['animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', pingColor]"
      />
      <span :class="['relative inline-flex rounded-full h-2 w-2', dotColor]" />
    </span>
    <span v-if="label" class="tracking-tight">{{ label }}</span>
  </span>
</template>
