<script setup lang="ts">
import Badge from '../ui/badge/Badge.vue'

interface Props {
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'muted' | 'outline' | 'neutral'
  label: string
  size?: 'sm' | 'md'
  dot?: boolean
}

withDefaults(defineProps<Props>(), {
  variant: 'muted',
  size: 'sm',
  dot: false,
})
</script>

<template>
  <Badge :variant="variant" :size="size" :dot="dot">
    <slot name="icon" />
    {{ label }}
  </Badge>
</template>
