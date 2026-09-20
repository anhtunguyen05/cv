<script setup lang="ts">
interface Props {
  title: string
  description?: string
  level?: 'h1' | 'h2' | 'h3'
  kicker?: string
}

withDefaults(defineProps<Props>(), {
  level: 'h2',
})
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
      <div v-if="kicker" class="text-xs font-semibold text-primary uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
        <slot name="kicker">{{ kicker }}</slot>
      </div>

      <div class="flex items-center gap-3">
        <component
          :is="level"
          :class="{
            'text-2xl sm:text-3xl font-semibold tracking-tight text-text': level === 'h1',
            'text-xl font-semibold tracking-tight text-text': level === 'h2',
            'text-base sm:text-lg font-semibold tracking-tight text-text': level === 'h3',
          }"
        >
          {{ title }}
        </component>
        <slot name="badge" />
      </div>

      <p v-if="description" class="mt-2 text-sm text-text-muted max-w-3xl leading-relaxed">
        {{ description }}
      </p>
    </div>

    <div v-if="$slots.action" class="flex-shrink-0 flex items-center gap-2.5 pt-1">
      <slot name="action" />
    </div>
  </div>
</template>
