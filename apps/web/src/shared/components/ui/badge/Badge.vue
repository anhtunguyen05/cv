<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'muted'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'muted',
})

const variantClass = computed(() => {
  const map: Record<NonNullable<Props['variant']>, string> = {
    primary: 'bg-[#eef2ff] text-[#4338ca]',
    success: 'bg-[#ecfdf5] text-[#059669]',
    warning: 'bg-[#fffbeb] text-[#b45309]',
    danger: 'bg-[#fef2f2] text-[#dc2626]',
    muted: 'bg-[#f1f5f9] text-[#64748b]',
  }
  return map[props.variant]
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
      variantClass,
    ]"
  >
    <slot />
  </span>
</template>
