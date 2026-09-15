<script setup lang="ts">
// Always left-aligned — centered headings banned (DESIGN_VARIANCE=8)
interface Props {
  title: string
  description?: string
  level?: 'h1' | 'h2' | 'h3'
}

withDefaults(defineProps<Props>(), {
  level: 'h2',
})
</script>

<template>
  <div class="flex items-start justify-between gap-4">
    <div>
      <component
        :is="level"
        :class="{
          'text-2xl font-semibold tracking-tight text-[#0f172a]': level === 'h2',
          'text-xl font-semibold tracking-tight text-[#0f172a]': level === 'h3',
          'text-3xl font-bold tracking-tight text-[#0f172a]': level === 'h1',
        }"
      >
        {{ title }}
      </component>
      <p v-if="description" class="mt-1 text-sm text-[#64748b]">{{ description }}</p>
    </div>
    <div v-if="$slots.action" class="flex-shrink-0 flex items-center gap-2">
      <slot name="action" />
    </div>
  </div>
</template>
