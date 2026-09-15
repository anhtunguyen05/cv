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
    :class="[
      'w-full h-9 px-3 rounded-md text-sm text-[#0f172a] placeholder:text-[#94a3b8]',
      'border transition-colors duration-150',
      'focus:outline-none focus:ring-2 focus:ring-offset-0',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-[#f8fafc]',
      error
        ? 'border-[#ef4444] focus:ring-[#ef4444]/30 bg-[#fef2f2]'
        : 'border-[#e2e8f0] bg-white focus:border-[#6366f1] focus:ring-[#6366f1]/20',
    ]"
  />
</template>
