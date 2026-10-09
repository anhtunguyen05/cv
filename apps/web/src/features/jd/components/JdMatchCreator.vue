<script setup lang="ts">
import Button from '@/shared/components/ui/button/Button.vue'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import type { JdMatchContract } from '../composables/useJdEditorController'
defineProps<{ match: JdMatchContract }>()
</script>
<template>
  <div class="rounded-xl border border-border bg-surface-muted/40 p-4 space-y-3">
    <div>
      <h3 class="text-sm font-bold text-text">Generate a Match Report</h3>
      <p class="text-xs text-text-muted">
        Choose a saved CV Version. The server pins this report to the current analyzed revision.
      </p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <label class="flex-1 flex flex-col gap-1.5 text-sm font-semibold text-text" for="cv-version"
        >CV Version
        <select
          id="cv-version"
          :value="match.selectedCvVersionId.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @change="match.setSelectedCvVersionId(($event.target as HTMLSelectElement).value)"
        >
          <option value="">Select a CV Version</option>
          <option
            v-for="version in match.versionsQuery.data.value?.items ?? []"
            :key="version.id"
            :value="version.id"
          >
            {{ version.name }}
          </option>
        </select>
      </label>
      <Button
        type="button"
        :loading="match.matchMutation.isPending.value"
        :disabled="
          !match.selectedCvVersionId.value ||
          match.matchMutation.isPending.value ||
          match.hasUnsavedChanges.value
        "
        @click="match.createReport"
        >Create Match Report</Button
      >
    </div>
    <p v-if="match.versionsQuery.isLoading.value" class="text-xs text-text-muted">
      Loading CV Versions…
    </p>
    <PaginationNav
      v-if="(match.versionsQuery.data.value?.lastPage ?? 1) > 1"
      :page="match.navigation.page.value"
      :last-page="match.navigation.lastPage.value"
      label="CV Version pages"
      previous-label="Previous versions"
      next-label="Next versions"
      appearance="text"
      @previous="match.navigation.previous"
      @next="match.navigation.next"
    />
    <p v-else-if="match.versionsQuery.isError.value" class="text-xs text-danger">
      Unable to load CV Versions.
      <button type="button" class="underline" @click="match.versionsQuery.refetch()">Retry</button>
    </p>
    <p v-if="match.hasUnsavedChanges.value" class="text-xs text-warning-text" role="status">
      Save this edited revision before analyzing or matching.
    </p>
    <p v-if="match.matchMutation.isError.value" class="text-xs text-danger" role="alert">
      {{
        match.matchMutation.error.value instanceof Error
          ? match.matchMutation.error.value.message
          : 'The Match Report could not be created.'
      }}
      <button
        v-if="match.matchRetryable.value"
        type="button"
        class="underline"
        @click="match.retryMatch"
      >
        Retry
      </button>
    </p>
  </div>
</template>
