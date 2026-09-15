<script setup lang="ts">
interface Props {
  status?: 'active' | 'idle' | 'processing' | 'error'
  label?: string
}

withDefaults(defineProps<Props>(), {
  status: 'active',
})

const colorMap: Record<string, string> = {
  active: 'bg-[#10b981]',
  idle: 'bg-[#64748b]',
  processing: 'bg-[#6366f1]',
  error: 'bg-[#ef4444]',
}
</script>

<template>
  <span class="inline-flex items-center gap-1.5">
    <span
      :class="['w-2 h-2 rounded-full', colorMap[status], status !== 'idle' && 'animate-[pulse-dot_2s_ease-in-out_infinite]']"
      aria-hidden="true"
    />
    <span v-if="label" class="text-xs text-[#64748b]">{{ label }}</span>
  </span>
</template>
