<script setup lang="ts">
// Enforces consistent strokeWidth=1.5 across all lucide icons.
// Usage: <AppIcon :icon="CheckCircle" /> where CheckCircle is a lucide component.
import type { Component } from 'vue'

interface Props {
  icon: Component
  size?: number
  strokeWidth?: number
  class?: string
}

withDefaults(defineProps<Props>(), {
  size: 16,
  strokeWidth: 1.5,
})
</script>

<template>
  <component
    :is="icon"
    :size="size"
    :stroke-width="strokeWidth"
    :class="$props.class"
    aria-hidden="true"
  />
</template>
