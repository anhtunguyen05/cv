<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  score: number // 0-100
}

const props = defineProps<Props>()

// SVG circle math
const radius = 54
const circumference = 2 * Math.PI * radius
const offset = computed(() => circumference - (props.score / 100) * circumference)

const scoreColor = computed(() => {
  if (props.score >= 80) return 'var(--color-success)'
  if (props.score >= 60) return 'var(--color-primary)'
  if (props.score >= 40) return 'var(--color-warning)'
  return 'var(--color-danger)'
})

const scoreLabel = computed(() => {
  if (props.score >= 80) return 'High Alignment'
  if (props.score >= 60) return 'Solid Match'
  if (props.score >= 40) return 'Moderate Fit'
  return 'Low Coverage'
})
</script>

<template>
  <div class="flex flex-col items-center gap-2.5 py-1">
    <div class="relative w-36 h-36 flex items-center justify-center">
      <svg class="w-full h-full -rotate-90" viewBox="0 0 130 130">
        <!-- Background track -->
        <circle
          cx="65"
          cy="65"
          :r="radius"
          fill="none"
          stroke="var(--color-surface-muted)"
          stroke-width="9"
        />
        <!-- Progress arc -->
        <circle
          cx="65"
          cy="65"
          :r="radius"
          fill="none"
          :stroke="scoreColor"
          stroke-width="9"
          stroke-linecap="round"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="offset"
          style="transition: stroke-dashoffset 1s cubic-bezier(0.16, 1, 0.3, 1)"
        />
      </svg>

      <!-- Centered score -->
      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span
          class="text-3xl sm:text-4xl font-extrabold text-text font-mono leading-none tracking-tight"
        >
          {{ score }}
        </span>
        <span class="text-xs font-medium text-text-subtle mt-1">/ 100 PTS</span>
      </div>
    </div>

    <div class="text-center space-y-0.5">
      <p class="text-xs sm:text-sm font-bold tracking-tight" :style="{ color: scoreColor }">
        {{ scoreLabel }}
      </p>
      <p class="text-xs text-text-muted">ATS Evaluation Score</p>
    </div>
  </div>
</template>
