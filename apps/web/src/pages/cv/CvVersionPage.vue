<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import { getCvVersion } from '@/features/cv/api/cv.api'
import { useQuery } from '@tanstack/vue-query'

const route = useRoute()
const profileId = computed(() => String(route.params.id))
const versionId = computed(() => String(route.params.versionId))
const { data, isLoading, isError } = useQuery({
  queryKey: ['cv-versions', versionId],
  queryFn: () => getCvVersion(versionId.value),
})
const version = computed(() => data.value)
</script>

<template>
  <div class="flex w-full flex-col items-center">
    <div class="no-print mb-6 flex w-full max-w-[794px] items-center justify-between">
      <RouterLink :to="`/cv/${profileId}/edit`">
        <AppButton size="sm" variant="outline">Back to editor</AppButton>
      </RouterLink>
      <span v-if="version" class="text-sm text-text-muted"
        >Immutable Version · {{ version.name }}</span
      >
    </div>
    <p v-if="isLoading" role="status">Loading Version…</p>
    <p v-else-if="isError" role="alert">Unable to load this Version.</p>
    <div v-else-if="version" class="overflow-x-auto pb-12">
      <CvPreviewDocument :data="version.snapshot" />
    </div>
  </div>
</template>
