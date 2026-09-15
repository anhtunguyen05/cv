<script setup lang="ts">
interface Props {
  value: number
  max?: number
  label?: string
  color?: 'primary' | 'success' | 'warning' | 'danger'
}

const props = withDefaults(defineProps<Props>(), {
  max: 100,
  color: 'primary',
})

const colorClass: Record<string, string> = {
  primary: 'bg-[#6366f1]',
  success: 'bg-[#10b981]',
  warning: 'bg-[#f59e0b]',
  danger: 'bg-[#ef4444]',
}

const percent = () => Math.min(100, Math.max(0, (props.value / props.max) * 100))
</script>

<template>
  <div class="w-full">
    <div
      v-if="label"
      class="flex justify-between text-xs text-[#64748b] mb-1.5 font-medium"
    >
      <span>{{ label }}</span>
      <span>{{ Math.round(percent()) }}%</span>
    </div>
    <div
      class="w-full h-2 bg-[#f1f5f9] rounded-full overflow-hidden"
      role="progressbar"
      :aria-valuenow="value"
      :aria-valuemax="max"
      :aria-label="label"
    >
      <div
        :class="['h-full rounded-full transition-all duration-500 ease-out', colorClass[color]]"
        :style="{ width: `${percent()}%` }"
      />
    </div>
  </div>
</template>
