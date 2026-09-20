<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'elevated' | 'subtle' | 'interactive' | 'glass' | 'dark'
  padding?: 'none' | 'sm' | 'md' | 'lg'
  noPadding?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  padding: 'md',
  noPadding: false,
})

const variantClass = computed(() => {
  switch (props.variant) {
    case 'elevated':
      return 'bg-white border border-border shadow-card-elevated'
    case 'subtle':
      return 'bg-surface border border-border/80 shadow-hairline'
    case 'interactive':
      return 'bg-white border border-border hover:border-border-hover hover:shadow-card-hover hover:-translate-y-[1px] transition-[transform,box-shadow,border-color] duration-200 cursor-pointer'
    case 'glass':
      return 'bg-white/80 backdrop-blur-md border border-white/60 shadow-glass'
    case 'dark':
      return 'bg-text text-white border border-slate-800 shadow-dark-panel'
    default:
      return 'bg-white border border-border shadow-card'
  }
})

const paddingClass = computed(() => {
  if (props.noPadding || props.padding === 'none') return ''
  switch (props.padding) {
    case 'sm':
      return 'p-4 sm:p-5'
    case 'lg':
      return 'p-6 sm:p-8'
    case 'md':
    default:
      return 'p-5 sm:p-6'
  }
})
</script>

<template>
  <div
    :class="[
      'rounded-xl transition-[transform,box-shadow,border-color] duration-150',
      variantClass,
      paddingClass,
    ]"
  >
    <slot />
  </div>
</template>
