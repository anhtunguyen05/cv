<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useCvVersionsQuery } from '@/features/cv/api/cv.queries'
import Button from '@/shared/components/ui/button/Button.vue'
import { ROUTES } from '@/shared/constants/routes'

const route = useRoute()
const profileId = computed(() => String(route.params.id))
const versionsQuery = useCvVersionsQuery(profileId)
</script>

<template>
  <div class="mx-auto w-full max-w-2xl space-y-6">
    <div>
      <p class="text-xs font-bold uppercase tracking-wider text-primary">Immutable preview</p>
      <h1 class="mt-1 text-2xl font-bold text-text">Choose a saved CV Version</h1>
      <p class="mt-2 text-sm text-text-muted">
        Preview and export use only a saved snapshot, never the mutable Profile draft.
      </p>
    </div>
    <p v-if="versionsQuery.isLoading.value" role="status">Loading saved Versions…</p>
    <p v-else-if="versionsQuery.isError.value" role="alert" class="text-sm text-danger-text">
      Unable to load saved Versions.
    </p>
    <div v-else-if="versionsQuery.data.value?.length" class="space-y-3">
      <div
        v-for="version in versionsQuery.data.value"
        :key="version.id"
        class="flex items-center justify-between gap-4 rounded-xl border border-border bg-white p-4"
      >
        <div>
          <p class="font-semibold text-text">{{ version.name }}</p>
          <p class="text-xs text-text-muted">
            Source revision {{ version.source_profile_revision }}
          </p>
        </div>
        <RouterLink :to="ROUTES.CV_VERSION_TEMPLATES(version.id)">
          <Button size="sm">Choose template</Button>
        </RouterLink>
      </div>
    </div>
    <div v-else class="rounded-xl border border-border bg-white p-5">
      <p class="text-sm text-text-muted">
        Create an immutable Version in the CV editor before previewing.
      </p>
      <RouterLink class="mt-4 inline-block" :to="ROUTES.CV_EDIT(profileId)">
        <Button size="sm" variant="outline">Open CV editor</Button>
      </RouterLink>
    </div>
  </div>
</template>
