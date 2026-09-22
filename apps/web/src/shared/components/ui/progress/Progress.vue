<script setup lang="ts">
interface Props {
  value: number
  max?: number
  label?: string
  color?: 'primary' | 'success' | 'warning' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  showValue?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  max: 100,
  color: 'primary',
  size: 'md',
  showValue: true,
})

const colorClass: Record<string, string> = {
  primary: 'bg-primary',
  success: 'bg-success',
  warning: 'bg-warning',
  danger: 'bg-danger',
}

const heightClass: Record<string, string> = {
  sm: 'h-1.5',
  md: 'h-2',
  lg: 'h-2.5',
}

const percent = () => Math.min(100, Math.max(0, (props.value / props.max) * 100))
</script>

<template>
  <div class="w-full">
    <div v-if="label || showValue" class="flex justify-between items-center text-xs mb-1.5">
      <span v-if="label" class="text-text font-medium">{{ label }}</span>
      <span v-if="showValue" class="font-mono text-xs font-semibold text-text-quiet"
        >{{ Math.round(percent()) }}%</span
      >
    </div>
    <div
      :class="[
        'w-full bg-surface-muted rounded-full overflow-hidden border border-border/60',
        heightClass[size],
      ]"
      role="progressbar"
      :aria-valuenow="value"
      :aria-valuemax="max"
      :aria-label="label"
    >
      <div
        :class="['h-full rounded-full transition-all duration-700 ease-out', colorClass[color]]"
        :style="{ width: `${percent()}%` }"
      />
    </div>
  </div>
</template>
