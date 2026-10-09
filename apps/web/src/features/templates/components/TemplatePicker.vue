<script setup lang="ts">
import { ArrowRight, LayoutTemplate, ShieldCheck } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { CvVersionSelector } from '@/features/cv'
import TemplateCard from '@/features/templates/components/TemplateCard.vue'
import SearchInput from '@/shared/components/molecules/SearchInput.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import { ROUTES } from '@/shared/constants/routes'
import { useTemplatePickerController } from '../composables/useTemplatePickerController'

const {
  search,
  selected,
  versionId,
  templatesQuery,
  versionsQuery,
  savedVersions,
  versionNavigation,
  filtered,
  onSelect,
  applyTemplate,
  openDashboard,
  resetSearchOrOpenDashboard,
} = useTemplatePickerController()
</script>

<template>
  <div class="space-y-6">
    <div
      class="flex flex-col justify-between gap-4 border-b border-border/80 pb-4 sm:flex-row sm:items-center"
    >
      <div>
        <div class="mb-1 flex items-center gap-2">
          <RouterLink :to="ROUTES.DASHBOARD" class="text-xs text-text-muted hover:text-text"
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

      <Button v-if="selected && versionId" size="md" @click="applyTemplate">
        <span>Preview “{{ selected.name }}”</span>
        <ArrowRight :size="15" />
      </Button>
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
      <Button size="sm" variant="outline" @click="versionsQuery.refetch()">Retry</Button>
    </div>
    <div v-else-if="!versionId && savedVersions.length" class="space-y-3">
      <h2 class="text-sm font-semibold text-text">Choose a saved Version</h2>
      <CvVersionSelector
        :versions="savedVersions"
        :page="versionNavigation.page.value"
        :last-page="versionNavigation.lastPage.value"
        variant="card"
        :destination="(version) => ROUTES.CV_VERSION_TEMPLATES(version.id)"
        @previous="versionNavigation.previous"
        @next="versionNavigation.next"
      />
    </div>
    <EmptyState
      v-else-if="!versionId"
      :icon="LayoutTemplate"
      title="Save a CV Version first"
      description="Templates can only be applied to an immutable saved Version."
      action-label="Open dashboard"
      @action="openDashboard"
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
        <Button size="sm" variant="outline" class="mt-3" @click="templatesQuery.refetch()">
          Retry
        </Button>
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
        @action="resetSearchOrOpenDashboard"
      />
    </template>
  </div>
</template>
