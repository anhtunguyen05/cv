<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  score: number // 0-100
}

const props = defineProps<Props>()

// SVG circle math
const radius = 52
const circumference = 2 * Math.PI * radius
const offset = computed(() => circumference - (props.score / 100) * circumference)

const scoreColor = computed(() => {
  if (props.score >= 80) return '#10b981'
  if (props.score >= 60) return '#6366f1'
  if (props.score >= 40) return '#f59e0b'
  return '#ef4444'
})

const scoreLabel = computed(() => {
  if (props.score >= 80) return 'Excellent'
  if (props.score >= 60) return 'Good'
  if (props.score >= 40) return 'Fair'
  return 'Low'
})
</script>

<template>
  <div class="flex flex-col items-center gap-3">
    <div class="relative w-36 h-36">
      <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
        <!-- Background track -->
        <circle
          cx="60" cy="60"
          :r="radius"
          fill="none"
          stroke="#e2e8f0"
          stroke-width="8"
        />
        <!-- Progress arc -->
        <circle
          cx="60" cy="60"
          :r="radius"
          fill="none"
          :stroke="scoreColor"
          stroke-width="8"
          stroke-linecap="round"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="offset"
          style="transition: stroke-dashoffset 0.8s cubic-bezier(0.16,1,0.3,1);"
        />
      </svg>
      <!-- Score number -->
      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-3xl font-bold text-[#0f172a]" style="line-height: 1;">{{ score }}</span>
        <span class="text-xs text-[#64748b]">/ 100</span>
      </div>
    </div>
    <div class="text-center">
      <p class="text-sm font-semibold" :style="{ color: scoreColor }">{{ scoreLabel }} match</p>
      <p class="text-xs text-[#64748b] mt-0.5">Overall compatibility</p>
    </div>
  </div>
</template>
