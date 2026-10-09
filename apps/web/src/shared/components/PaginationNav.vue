<script setup lang="ts">
import Button from '@/shared/components/ui/button/Button.vue'

withDefaults(
  defineProps<{
    page: number
    lastPage: number
    label: string
    previousLabel?: string
    nextLabel?: string
    size?: 'sm' | 'md'
    appearance?: 'button' | 'text'
  }>(),
  {
    previousLabel: 'Previous',
    nextLabel: 'Next',
    size: 'md',
    appearance: 'button',
  },
)

const emit = defineEmits<{
  previous: []
  next: []
}>()
</script>

<template>
  <nav
    v-if="lastPage > 1"
    class="flex items-center justify-between"
    :class="appearance === 'text' ? 'text-xs text-text-muted' : ''"
    :aria-label="label"
  >
    <template v-if="appearance === 'text'">
      <button type="button" class="underline" :disabled="page <= 1" @click="emit('previous')">
        {{ previousLabel }}
      </button>
    </template>
    <Button
      v-else
      type="button"
      :size="size"
      variant="outline"
      :disabled="page <= 1"
      @click="emit('previous')"
    >
      {{ previousLabel }}
    </Button>
    <span class="text-xs text-text-muted" aria-live="polite"
      >Page {{ page }} of {{ lastPage }}</span
    >
    <template v-if="appearance === 'text'">
      <button type="button" class="underline" :disabled="page >= lastPage" @click="emit('next')">
        {{ nextLabel }}
      </button>
    </template>
    <Button
      v-else
      type="button"
      :size="size"
      variant="outline"
      :disabled="page >= lastPage"
      @click="emit('next')"
    >
      {{ nextLabel }}
    </Button>
  </nav>
</template>
