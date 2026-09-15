<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'outline' | 'ghost' | 'destructive'
  size?: 'sm' | 'md' | 'lg'
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
    'inline-flex items-center justify-center gap-2 font-medium rounded-md',
    'transition-all duration-150 ease-out',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
    'active:scale-[0.98] active:translate-y-px',
    'disabled:opacity-50 disabled:pointer-events-none select-none',
  ]

  const sizes: Record<string, string> = {
    sm: 'h-8 px-3 text-sm',
    md: 'h-9 px-4 text-sm',
    lg: 'h-11 px-6 text-base',
  }

  const variants: Record<string, string> = {
    default: 'bg-[#6366f1] text-white hover:bg-[#4f46e5] focus-visible:ring-[#6366f1]',
    outline:
      'border border-[#e2e8f0] bg-white text-[#0f172a] hover:bg-[#f8fafc] hover:border-[#6366f1] focus-visible:ring-[#6366f1]',
    ghost: 'text-[#0f172a] hover:bg-[#f1f5f9] focus-visible:ring-[#6366f1]',
    destructive: 'bg-[#ef4444] text-white hover:bg-[#dc2626] focus-visible:ring-[#ef4444]',
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
    <span v-if="loading" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" aria-hidden="true" />
    <slot />
  </component>
</template>
