<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { Edit3, ZoomIn, ZoomOut } from 'lucide-vue-next'
import { useCvProfileQuery } from '@/features/cv/api/cv.queries'

const route = useRoute()
const cvId = computed(() => String(route.params.id))
const { data, isLoading, isError } = useCvProfileQuery(cvId)
const zoom = ref(100)
const profile = computed(() => data.value)
</script>

<template>
  <div class="flex w-full flex-col items-center">
    <div class="no-print mb-6 flex w-full max-w-[794px] items-center justify-between gap-4">
      <RouterLink :to="`/cv/${cvId}/edit`"
        ><AppButton size="sm" variant="outline"
          ><Edit3 :size="14" /> Edit Sections</AppButton
        ></RouterLink
      >
      <div class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-1.5">
        <button type="button" aria-label="Zoom out" @click="zoom = Math.max(70, zoom - 10)">
          <ZoomOut :size="15" />
        </button>
        <span class="text-xs font-mono">{{ zoom }}%</span>
        <button type="button" aria-label="Zoom in" @click="zoom = Math.min(130, zoom + 10)">
          <ZoomIn :size="15" />
        </button>
      </div>
    </div>
    <p v-if="isLoading" role="status">Loading Profile…</p>
    <p v-else-if="isError" role="alert">Unable to load this Profile.</p>
    <div
      v-else-if="profile"
      class="overflow-x-auto pb-12"
      :style="{ transform: `scale(${zoom / 100})`, transformOrigin: 'top center' }"
    >
      <CvPreviewDocument :data="profile" />
    </div>
  </div>
</template>
