<script setup lang="ts">
import Button from '@/shared/components/ui/button/Button.vue'
import type { JdEditorController } from '../composables/useJdEditorController'
defineProps<{ controller: JdEditorController }>()
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
          :value="controller.selectedCvVersionId.value"
          class="h-10 px-3 rounded-lg border border-border bg-white font-normal focus:outline-none focus:ring-2 focus:ring-primary/20"
          @change="controller.setSelectedCvVersionId(($event.target as HTMLSelectElement).value)"
        >
          <option value="">Select a CV Version</option>
          <option
            v-for="version in controller.versionsQuery.data.value?.items ?? []"
            :key="version.id"
            :value="version.id"
          >
            {{ version.name }}
          </option>
        </select>
      </label>
      <Button
        type="button"
        :loading="controller.matchMutation.isPending.value"
        :disabled="
          !controller.selectedCvVersionId.value ||
          controller.matchMutation.isPending.value ||
          controller.hasUnsavedChanges.value
        "
        @click="controller.createReport"
        >Create Match Report</Button
      >
    </div>
    <p v-if="controller.versionsQuery.isLoading.value" class="text-xs text-text-muted">
      Loading CV Versions…
    </p>
    <div
      v-if="(controller.versionsQuery.data.value?.lastPage ?? 1) > 1"
      class="flex items-center justify-between text-xs text-text-muted"
    >
      <button
        type="button"
        class="underline"
        :disabled="controller.cvVersionPage.value <= 1"
        @click="controller.previousCvVersionPage"
      >
        Previous versions</button
      ><span
        >Page {{ controller.cvVersionPage.value }} of
        {{ controller.versionsQuery.data.value?.lastPage }}</span
      ><button
        type="button"
        class="underline"
        :disabled="
          controller.cvVersionPage.value >= (controller.versionsQuery.data.value?.lastPage ?? 1)
        "
        @click="controller.nextCvVersionPage"
      >
        Next versions
      </button>
    </div>
    <p v-else-if="controller.versionsQuery.isError.value" class="text-xs text-danger">
      Unable to load CV Versions.
      <button type="button" class="underline" @click="controller.versionsQuery.refetch()">
        Retry
      </button>
    </p>
    <p v-if="controller.hasUnsavedChanges.value" class="text-xs text-warning-text" role="status">
      Save this edited revision before analyzing or matching.
    </p>
    <p v-if="controller.matchMutation.isError.value" class="text-xs text-danger" role="alert">
      {{
        controller.matchMutation.error.value instanceof Error
          ? controller.matchMutation.error.value.message
          : 'The Match Report could not be created.'
      }}
      <button
        v-if="controller.matchRetryable.value"
        type="button"
        class="underline"
        @click="controller.retryMatch"
      >
        Retry
      </button>
    </p>
  </div>
</template>
