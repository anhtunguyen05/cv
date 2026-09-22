<script setup lang="ts">
// Skeleton shimmer element. Size via class prop.
// Usage: <Skeleton class="h-4 w-32" /> or <Skeleton class="h-10 w-full rounded-md" />
defineProps<{ class?: string }>()
</script>

<template>
  <div :class="['rounded-md skeleton-shimmer', $props.class]" aria-hidden="true" />
</template>
