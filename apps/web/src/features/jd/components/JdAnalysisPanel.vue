<script setup lang="ts">
import { RefreshCw } from 'lucide-vue-next'
import Button from '@/shared/components/ui/button/Button.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import type { JdEditorController } from '../composables/useJdEditorController'
import JdMatchCreator from './JdMatchCreator.vue'
defineProps<{ controller: JdEditorController }>()
</script>
<template>
  <Card
    v-if="controller.analysisQuery.isError.value || controller.analysisMutation.isError.value"
    class="space-y-3"
    role="alert"
  >
    <h2 class="text-lg font-bold text-text">Analysis unavailable</h2>
    <p class="text-sm text-text-muted">
      {{
        controller.analysisMutation.error.value instanceof Error
          ? controller.analysisMutation.error.value.message
          : controller.analysisQuery.error.value instanceof Error
            ? controller.analysisQuery.error.value.message
            : 'The analysis could not be loaded.'
      }}
    </p>
    <div class="flex flex-wrap gap-3">
      <Button
        v-if="controller.analysisQuery.isError.value"
        type="button"
        variant="outline"
        @click="controller.analysisQuery.refetch()"
        ><RefreshCw :size="15" aria-hidden="true" /> Retry analysis load</Button
      ><Button type="button" variant="outline" @click="controller.analyze">Analyze again</Button
      ><Button
        v-if="controller.analysisConflict.value"
        type="button"
        variant="outline"
        @click="controller.reloadCurrentRevision"
        >Reload current revision</Button
      >
    </div>
  </Card>
  <Card v-if="controller.analysis.value" class="space-y-4" aria-live="polite">
    <div>
      <h2 class="text-lg font-bold text-text">Deterministic analysis</h2>
      <p class="text-xs text-text-muted">
        Analysis rule {{ controller.analysis.value.analysis_rule_version }} · revision
        {{ controller.analysis.value.job_description_revision_id }} · Analysis ID
        {{ controller.analysis.value.id }}
      </p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
      <div
        v-for="(signal, name) in controller.analysis.value.signals"
        :key="name"
        class="rounded-lg border border-border p-3"
      >
        <span class="font-semibold text-text capitalize">{{
          String(name).replaceAll('_', ' ')
        }}</span>
        <p class="text-xs text-text-muted mt-1">
          {{ signal.state
          }}<span v-if="'value' in signal && signal.value">: {{ signal.value }}</span
          ><span v-else-if="'items' in signal && signal.items.length"
            >:
            {{
              signal.items.map((item) => (typeof item === 'string' ? item : item.label)).join(', ')
            }}</span
          >
        </p>
      </div>
    </div>
    <JdMatchCreator :controller />
  </Card>
</template>
