<script setup lang="ts">
import { RouterLink } from 'vue-router'
import Button from '@/shared/components/ui/button/Button.vue'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import type { CvVersionSummary } from '../types/cv.types'

withDefaults(
  defineProps<{
    versions: CvVersionSummary[]
    page: number
    lastPage: number
    destination: (version: CvVersionSummary) => string
    variant?: 'preview' | 'card'
    actionLabel?: string
  }>(),
  {
    variant: 'preview',
    actionLabel: 'Choose template',
  },
)

const emit = defineEmits<{
  previous: []
  next: []
}>()
</script>

<template>
  <div v-if="variant === 'preview'" class="space-y-3">
    <div
      v-for="version in versions"
      :key="version.id"
      class="flex items-center justify-between gap-4 rounded-xl border border-border bg-white p-4"
    >
      <div>
        <p class="font-semibold text-text">{{ version.name }}</p>
        <p class="text-xs text-text-muted">Source revision {{ version.source_profile_revision }}</p>
      </div>
      <RouterLink :to="destination(version)">
        <Button size="sm">{{ actionLabel }}</Button>
      </RouterLink>
    </div>
  </div>
  <div v-else class="grid gap-3 sm:grid-cols-2">
    <RouterLink
      v-for="version in versions"
      :key="version.id"
      :to="destination(version)"
      class="rounded-lg border border-border bg-white p-4 transition hover:border-primary-border hover:shadow-sm"
    >
      <span class="font-semibold text-text">{{ version.name }}</span>
      <span class="mt-1 block text-xs text-text-muted">Saved {{ version.created_at }}</span>
    </RouterLink>
  </div>
  <PaginationNav
    v-if="lastPage > 1"
    class="mt-3"
    :page="page"
    :last-page="lastPage"
    label="Saved Version pages"
    size="sm"
    @previous="emit('previous')"
    @next="emit('next')"
  />
</template>
