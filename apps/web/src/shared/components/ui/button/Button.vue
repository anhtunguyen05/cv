<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'outline' | 'ghost' | 'destructive' | 'secondary' | 'glass'
  size?: 'xs' | 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
  type?: 'button' | 'submit' | 'reset'
  as?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
  disabled: false,
  loading: false,
  type: 'button',
  as: 'button',
})

const classes = computed(() => {
  const base = [
    'inline-flex shrink-0 items-center justify-center whitespace-nowrap font-medium select-none',
    'transition-colors duration-150 ease-out cursor-pointer',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
    'active:scale-[0.98] active:translate-y-[0.5px]',
    'disabled:opacity-50 disabled:pointer-events-none disabled:cursor-not-allowed',
  ]

  const sizes: Record<string, string> = {
    xs: 'h-8 px-2.5 text-xs font-medium rounded-md gap-1.5',
    sm: 'h-9 px-3 text-sm font-medium rounded-lg gap-1.5',
    md: 'h-10 px-4 text-sm font-medium rounded-lg gap-2',
    lg: 'h-11 px-5 sm:px-6 text-base font-semibold rounded-lg gap-2.5',
  }

  const variants: Record<string, string> = {
    default:
      'bg-primary text-white hover:bg-primary-hover shadow-control focus-visible:ring-primary',
    secondary: 'bg-surface-muted text-text hover:bg-border focus-visible:ring-primary',
    outline:
      'border border-border bg-white text-text hover:bg-surface hover:border-border-hover hover:text-primary-dark shadow-control-outline focus-visible:ring-primary',
    ghost: 'text-text-quiet hover:bg-surface-muted hover:text-text focus-visible:ring-primary',
    destructive:
      'bg-danger text-white hover:bg-danger-hover shadow-control focus-visible:ring-danger',
    glass:
      'bg-white/10 text-white border border-white/15 hover:bg-white/20 backdrop-blur-md focus-visible:ring-white/40',
  }

  return [...base, sizes[props.size], variants[props.variant]].join(' ')
})
</script>

<template>
  <component
    :is="as"
    :type="as === 'button' ? type : undefined"
    :disabled="disabled || loading"
    :class="classes"
  >
    <span
      v-if="loading"
      class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"
      aria-hidden="true"
    />
    <span v-if="loading" class="sr-only">Loading</span>
    <slot />
  </component>
</template>
