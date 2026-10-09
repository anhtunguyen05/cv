<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useCvVersionsPageQuery } from '@/features/cv/api/cv.queries'
import CvVersionSelector from '@/features/cv/components/CvVersionSelector.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import { ROUTES } from '@/shared/constants/routes'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'

const route = useRoute()
const profileId = computed(() => String(route.params.id))
const versionPage = ref(1)
const versionsQuery = useCvVersionsPageQuery(versionPage, profileId)
const versionNavigation = usePageNavigation({
  page: versionPage,
  lastPage: () => versionsQuery.data.value?.lastPage,
  isFetching: versionsQuery.isFetching,
})
const versions = computed(() => versionsQuery.data.value?.items ?? [])
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
    <CvVersionSelector
      v-else-if="versions.length"
      :versions="versions"
      :page="versionNavigation.page.value"
      :last-page="versionNavigation.lastPage.value"
      :destination="(version) => ROUTES.CV_VERSION_TEMPLATES(version.id)"
      @previous="versionNavigation.previous"
      @next="versionNavigation.next"
    />
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
