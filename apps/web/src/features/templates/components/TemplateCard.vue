<script setup lang="ts">
import { ref } from 'vue'
import type { CvTemplate } from '../types/template.types'
import { Check, ShieldCheck } from 'lucide-vue-next'
import AppButton from '@/shared/components/atoms/AppButton.vue'

interface Props {
  template: CvTemplate
  selected?: boolean
}

withDefaults(defineProps<Props>(), {
  selected: false,
})

const emit = defineEmits<{ select: [template: CvTemplate] }>()

// Spotlight border effect
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
  <div class="flex flex-col h-full group">
    <div
      ref="cardRef"
      :class="[
        'relative rounded-xl overflow-hidden border-2 cursor-pointer transition-all duration-200 bg-white flex flex-col flex-1',
        selected
          ? 'border-primary shadow-card-selected'
          : 'border-border hover:border-primary-border hover:shadow-card-lift hover:-translate-y-1',
      ]"
      @mousemove="onMouseMove"
      @mouseleave="onMouseLeave"
      @click="emit('select', template)"
    >
      <!-- Spotlight overlay -->
      <div
        class="pointer-events-none absolute inset-0 z-10 transition-opacity duration-300 rounded-xl"
        :style="{
          opacity: spotlight.opacity,
          background: `radial-gradient(250px circle at ${spotlight.x}px ${spotlight.y}px, rgba(99,102,241,0.09), transparent 70%)`,
        }"
      />

      <!-- Template preview document mockup -->
      <div class="bg-surface aspect-[3/4] p-4 flex items-center justify-center overflow-hidden relative border-b border-border">
        <!-- Top ATS tag -->
        <div class="absolute top-3 left-3 z-20 flex items-center gap-1.5 text-xs font-semibold text-success-text bg-success-muted border border-success-border px-2.5 py-0.5 rounded-full shadow-2xs">
          <ShieldCheck :size="12" />
          <span>98% ATS Score</span>
        </div>

        <!-- Selected indicator pill -->
        <div
          v-if="selected"
          class="absolute top-3 right-3 z-20 w-6 h-6 bg-primary rounded-full flex items-center justify-center shadow-md animate-in zoom-in-50 duration-150"
        >
          <Check :size="14" :stroke-width="3" class="text-white" />
        </div>

        <!-- Realistic Document Silhouette Graphic -->
        <div class="w-40 h-56 bg-white rounded-md shadow-paper-preview border border-border p-3 space-y-2 select-none group-hover:scale-[1.02] transition-transform duration-200">
          <!-- Document Header -->
          <div class="space-y-1.5 pb-2 border-b border-border">
            <div class="h-2.5 bg-text rounded-sm w-3/5" />
            <div class="flex gap-1.5">
              <div class="h-1 bg-text-subtle rounded-xs w-1/4" />
              <div class="h-1 bg-text-subtle rounded-xs w-1/4" />
              <div class="h-1 bg-text-subtle rounded-xs w-1/4" />
            </div>
          </div>

          <!-- Summary section -->
          <div class="space-y-1.5">
            <div class="h-1 bg-primary rounded-xs w-1/3" />
            <div class="h-1 bg-border-hover rounded-xs w-full" />
            <div class="h-1 bg-border-hover rounded-xs w-5/6" />
          </div>

          <!-- Skills section -->
          <div class="space-y-1.5 pt-1">
            <div class="h-1 bg-primary rounded-xs w-1/4" />
            <div class="flex gap-1.5 flex-wrap">
              <div class="h-2 bg-surface-muted border border-border rounded-xs w-7" />
              <div class="h-2 bg-surface-muted border border-border rounded-xs w-9" />
              <div class="h-2 bg-surface-muted border border-border rounded-xs w-6" />
              <div class="h-2 bg-surface-muted border border-border rounded-xs w-8" />
            </div>
          </div>

          <!-- Projects section -->
          <div class="space-y-1.5 pt-1">
            <div class="h-1 bg-primary rounded-xs w-1/4" />
            <div class="h-2 bg-text/70 rounded-xs w-1/2" />
            <div class="h-1 bg-border-hover rounded-xs w-full" />
            <div class="h-1 bg-border-hover rounded-xs w-4/5" />
          </div>
        </div>
      </div>

      <!-- Card Details Footer -->
      <div class="p-4 bg-white space-y-1 flex-1 flex flex-col justify-between">
        <div>
          <h3 class="text-sm sm:text-base font-bold text-text">{{ template.name }}</h3>
          <p class="text-xs text-text-muted mt-0.5">{{ template.type }}</p>
        </div>

        <div class="pt-3">
          <AppButton
            :variant="selected ? 'default' : 'outline'"
            size="sm"
            class="w-full"
            @click.stop="emit('select', template)"
          >
            {{ selected ? 'Selected' : 'Use Template' }}
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
