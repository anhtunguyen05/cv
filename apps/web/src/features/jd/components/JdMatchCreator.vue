<script setup lang="ts">
import { computed, toRef } from 'vue'
import Button from '@/shared/components/ui/button/Button.vue'
import Label from '@/shared/components/ui/label/Label.vue'
import PaginationNav from '@/shared/components/molecules/PaginationNav.vue'
import Select from '@/shared/components/ui/select/Select.vue'
import type { JdMatchContract } from '../composables/useJdEditorController'

const props = defineProps<{ match: JdMatchContract }>()
const match = toRef(props, 'match')
const versionOptions = computed(() =>
  (match.value.versionsQuery.data.value?.items ?? []).map((version) => ({
    value: version.id,
    label: version.name,
  })),
)
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
      <div class="flex-1 flex flex-col gap-1.5 text-sm font-semibold text-text">
        <Label for="cv-version" class="font-semibold">CV Version</Label>
        <Select
          :model-value="match.selectedCvVersionId.value || undefined"
          :options="versionOptions"
          placeholder="Select a CV Version"
          clearable
          id="cv-version"
          class="h-10 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @update:model-value="match.setSelectedCvVersionId"
        />
      </div>
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
      <Button
        type="button"
        variant="link"
        class="text-inherit underline"
        @click="match.versionsQuery.refetch()"
        >Retry</Button
      >
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
      <Button
        v-if="match.matchRetryable.value"
        type="button"
        variant="link"
        class="text-inherit underline"
        @click="match.retryMatch"
      >
        Retry
      </Button>
    </p>
  </div>
</template>
