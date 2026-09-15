<script setup lang="ts">
import Badge from '../ui/badge/Badge.vue'

interface Props {
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'muted'
  label: string
}

withDefaults(defineProps<Props>(), {
  variant: 'muted',
})
</script>

<template>
  <Badge :variant="variant">
    <slot name="icon" />
    {{ label }}
  </Badge>
</template>
