<script setup lang="ts">
interface Props {
  error?: boolean
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  error: false,
  disabled: false,
})
</script>

<template>
  <input
    v-bind="$attrs"
    :disabled="disabled"
    :aria-invalid="error || undefined"
    :class="[
      'w-full h-10 px-3.5 rounded-lg text-sm text-text placeholder:text-text-subtle',
      'border transition-[border-color,box-shadow,background-color] duration-150',
      'focus:outline-none focus:ring-2 focus:ring-offset-0',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-surface',
      error
        ? 'border-danger focus:ring-danger/30 bg-danger-muted'
        : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
    ]"
  />
</template>
