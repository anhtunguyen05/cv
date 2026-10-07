<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import TemplateCard from '@/features/templates/components/TemplateCard.vue'
import type { CvTemplate } from '@/features/templates/types/template.types'
import SearchInput from '@/shared/components/molecules/SearchInput.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { getTemplates } from '@/features/templates/api/templates.api'
import { getCvVersions } from '@/features/cv/api/cv.api'
import { LayoutTemplate, ArrowRight, ShieldCheck } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const search = ref('')
const selected = ref<CvTemplate | null>(null)
const versionId = computed(() => {
  const value = route.params.versionId
  return typeof value === 'string' ? value : ''
})

const templatesQuery = useQuery({
  queryKey: ['templates'],
  queryFn: getTemplates,
})
const versionsQuery = useQuery({
  queryKey: ['cv-versions', 'template-entry'],
  queryFn: () => getCvVersions(),
  enabled: () => !versionId.value,
})

const templates = computed(() => templatesQuery.data.value ?? [])
const savedVersions = computed(() => versionsQuery.data.value ?? [])
const filtered = computed(() => {
  const query = search.value.toLowerCase().trim()
  if (!query) return templates.value
  return templates.value.filter((template) => {
    return `${template.name} ${template.description ?? ''} ${template.version}`
      .toLowerCase()
      .includes(query)
  })
})

watch(
  templates,
  (available) => {
    if (!selected.value && available[0]) selected.value = available[0]
  },
  { immediate: true },
)

function onSelect(template: CvTemplate) {
  selected.value =
    selected.value?.id === template.id && selected.value.version === template.version
      ? null
      : template
}

function applyTemplate() {
  if (!selected.value || !versionId.value) return
  const query = new URLSearchParams({
    template_id: selected.value.id,
    template_version: selected.value.version,
  })
  void router.push(`/cv-versions/${versionId.value}/preview?${query.toString()}`)
}
</script>

<template>
  <div class="space-y-6">
    <div
      class="flex flex-col justify-between gap-4 border-b border-border/80 pb-4 sm:flex-row sm:items-center"
    >
      <div>
        <div class="mb-1 flex items-center gap-2">
          <RouterLink to="/dashboard" class="text-xs text-text-muted hover:text-text"
            >Dashboard</RouterLink
          >
          <span class="text-xs text-border-hover">/</span>
          <span class="text-xs font-bold uppercase tracking-wider text-primary">Templates</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-text sm:text-3xl">Choose a template</h1>
        <p class="mt-1 max-w-2xl text-xs leading-relaxed text-text-muted sm:text-sm">
          Select an active layout for this saved CV Version. The reviewed source remains immutable.
        </p>
      </div>

      <AppButton v-if="selected && versionId" size="md" @click="applyTemplate">
        <span>Preview “{{ selected.name }}”</span>
        <ArrowRight :size="15" />
      </AppButton>
    </div>

    <p
      v-if="!versionId && versionsQuery.isLoading.value"
      role="status"
      class="text-sm text-text-muted"
    >
      Loading saved CV Versions…
    </p>
    <div v-else-if="!versionId && versionsQuery.isError.value" role="alert" class="space-y-3">
      <p class="text-sm text-danger-text">Unable to load saved CV Versions.</p>
      <AppButton size="sm" variant="outline" @click="versionsQuery.refetch()">Retry</AppButton>
    </div>
    <div v-else-if="!versionId && savedVersions.length" class="space-y-3">
      <h2 class="text-sm font-semibold text-text">Choose a saved Version</h2>
      <div class="grid gap-3 sm:grid-cols-2">
        <RouterLink
          v-for="version in savedVersions"
          :key="version.id"
          :to="`/cv-versions/${version.id}/templates`"
          class="rounded-lg border border-border bg-white p-4 transition hover:border-primary-border hover:shadow-sm"
        >
          <span class="font-semibold text-text">{{ version.name }}</span>
          <span class="mt-1 block text-xs text-text-muted">Saved {{ version.created_at }}</span>
        </RouterLink>
      </div>
    </div>
    <EmptyState
      v-else-if="!versionId"
      :icon="LayoutTemplate"
      title="Save a CV Version first"
      description="Templates can only be applied to an immutable saved Version."
      action-label="Open dashboard"
      @action="router.push('/dashboard')"
    />
    <template v-else>
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="w-full sm:w-80">
          <SearchInput v-model="search" placeholder="Search available templates..." />
        </div>
        <div
          class="flex items-center gap-1.5 self-start rounded-full border border-success-border bg-success-muted px-3 py-1.5 text-xs font-semibold text-success-text sm:self-auto"
        >
          <ShieldCheck :size="15" />
          <span>Server-validated templates</span>
        </div>
      </div>

      <p v-if="templatesQuery.isLoading.value" role="status" class="text-sm text-text-muted">
        Loading available templates…
      </p>
      <div
        v-else-if="templatesQuery.isError.value"
        role="alert"
        class="rounded-lg border border-danger-border bg-danger-muted p-4 text-sm text-danger-text"
      >
        <p>Unable to load templates. Refresh and try again.</p>
        <AppButton size="sm" variant="outline" class="mt-3" @click="templatesQuery.refetch()">
          Retry
        </AppButton>
      </div>
      <div
        v-else-if="filtered.length"
        class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6"
      >
        <TemplateCard
          v-for="template in filtered"
          :key="`${template.id}:${template.version}`"
          :template="template"
          :selected="selected?.id === template.id && selected?.version === template.version"
          @select="onSelect"
        />
      </div>
      <EmptyState
        v-else
        :icon="LayoutTemplate"
        :title="search ? 'No templates found' : 'No templates are currently available'"
        :description="
          search
            ? `No available templates match your search '${search}'.`
            : 'There are no active templates available for this Version.'
        "
        :action-label="search ? 'Reset search' : 'Return to dashboard'"
        @action="search ? (search = '') : router.push('/dashboard')"
      />
    </template>
  </div>
</template>
