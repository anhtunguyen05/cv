<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'muted' | 'outline' | 'neutral'
  size?: 'sm' | 'md'
  dot?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'muted',
  size: 'sm',
  dot: false,
})

const variantClass = computed(() => {
  const map: Record<NonNullable<Props['variant']>, string> = {
    primary: 'bg-primary-muted text-primary-dark border border-primary-border/60',
    success: 'bg-success-muted text-success-text border border-success-border/60',
    warning: 'bg-warning-muted text-warning-text border border-warning-border/60',
    danger: 'bg-danger-muted text-danger-text border border-danger-border/60',
    muted: 'bg-surface-muted text-text-quiet border border-border',
    outline: 'bg-transparent text-text-quiet border border-border-hover',
    neutral: 'bg-text text-white border border-transparent',
  }
  return map[props.variant]
})

const dotColor = computed(() => {
  const map: Record<NonNullable<Props['variant']>, string> = {
    primary: 'bg-primary',
    success: 'bg-success',
    warning: 'bg-warning',
    danger: 'bg-danger',
    muted: 'bg-text-subtle',
    outline: 'bg-text-muted',
    neutral: 'bg-white',
  }
  return map[props.variant]
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center font-medium rounded-full select-none transition-colors whitespace-nowrap',
      size === 'sm' ? 'px-2.5 py-1 text-xs gap-1.5' : 'px-3 py-1.5 text-xs font-semibold gap-2',
      variantClass,
    ]"
  >
    <span
      v-if="dot"
      :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', dotColor]"
      aria-hidden="true"
    />
    <slot />
  </span>
</template>
