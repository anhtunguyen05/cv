<script setup lang="ts">
import { ref } from 'vue'
import type { CvTemplate } from '../types/template.types'
import { Check } from 'lucide-vue-next'
import AppButton from '@/shared/components/atoms/AppButton.vue'

interface Props {
  template: CvTemplate
  selected?: boolean
}

withDefaults(defineProps<Props>(), {
  selected: false,
})

const emit = defineEmits<{ select: [template: CvTemplate] }>()

// Spotlight border effect — track mouse position relative to card
const cardRef = ref<HTMLDivElement | null>(null)
const spotlight = ref({ x: 0, y: 0, opacity: 0 })

function onMouseMove(e: MouseEvent) {
  const rect = cardRef.value?.getBoundingClientRect()
  if (!rect) return
  spotlight.value = {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top,
    opacity: 1,
  }
}

function onMouseLeave() {
  spotlight.value.opacity = 0
}
</script>

<template>
  <div
    ref="cardRef"
    :class="[
      'relative rounded-lg overflow-hidden border-2 cursor-pointer transition-all duration-200 group',
      selected
        ? 'border-[#6366f1] shadow-[0_0_0_3px_rgba(99,102,241,0.15)]'
        : 'border-[#e2e8f0] hover:border-[#c7d2fe]',
    ]"
    @mousemove="onMouseMove"
    @mouseleave="onMouseLeave"
    @click="emit('select', template)"
  >
    <!-- Spotlight overlay -->
    <div
      class="pointer-events-none absolute inset-0 z-10 transition-opacity duration-300 rounded-lg"
      :style="{
        opacity: spotlight.opacity,
        background: `radial-gradient(200px circle at ${spotlight.x}px ${spotlight.y}px, rgba(99,102,241,0.08), transparent 70%)`,
      }"
    />

    <!-- Preview image -->
    <div class="bg-[#f8fafc] aspect-[3/4] flex items-center justify-center overflow-hidden">
      <img
        v-if="template.preview_image"
        :src="template.preview_image"
        :alt="`${template.name} template preview`"
        class="w-full h-full object-cover object-top"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <!-- Placeholder document mockup -->
        <div class="w-24 h-32 bg-white rounded shadow-sm p-2 space-y-1.5">
          <div class="h-2 bg-[#6366f1]/40 rounded w-3/4" />
          <div class="h-1 bg-[#e2e8f0] rounded w-full" />
          <div class="h-1 bg-[#e2e8f0] rounded w-5/6" />
          <div class="h-1 bg-[#e2e8f0] rounded w-4/6 mt-2" />
          <div class="h-1 bg-[#e2e8f0] rounded w-full" />
          <div class="h-1 bg-[#e2e8f0] rounded w-5/6" />
        </div>
      </div>
    </div>

    <!-- Selected indicator -->
    <div
      v-if="selected"
      class="absolute top-2.5 right-2.5 z-20 w-6 h-6 bg-[#6366f1] rounded-full flex items-center justify-center"
    >
      <Check :size="13" :stroke-width="2.5" class="text-white" />
    </div>

    <!-- Footer -->
    <div class="p-3 bg-white border-t border-[#e2e8f0]">
      <p class="text-sm font-medium text-[#0f172a]">{{ template.name }}</p>
      <p class="text-xs text-[#64748b] mt-0.5">{{ template.type }}</p>
    </div>
  </div>

  <!-- Select button below card (outside card click area) -->
  <div class="mt-2">
    <AppButton
      :variant="selected ? 'default' : 'outline'"
      size="sm"
      class="w-full"
      @click="emit('select', template)"
    >
      {{ selected ? 'Selected' : 'Use template' }}
    </AppButton>
  </div>
</template>
