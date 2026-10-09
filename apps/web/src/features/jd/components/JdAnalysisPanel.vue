<script setup lang="ts">
import { RefreshCw } from 'lucide-vue-next'
import Button from '@/shared/components/ui/button/Button.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import type { JdAnalysisContract, JdMatchContract } from '../composables/useJdEditorController'
import JdMatchCreator from './JdMatchCreator.vue'
defineProps<{ analysis: JdAnalysisContract; match: JdMatchContract }>()
</script>
<template>
  <Card
    v-if="analysis.analysisQuery.isError.value || analysis.analysisMutation.isError.value"
    class="space-y-3"
    role="alert"
  >
    <h2 class="text-lg font-bold text-text">Analysis unavailable</h2>
    <p class="text-sm text-text-muted">
      {{
        analysis.analysisMutation.error.value instanceof Error
          ? analysis.analysisMutation.error.value.message
          : analysis.analysisQuery.error.value instanceof Error
            ? analysis.analysisQuery.error.value.message
            : 'The analysis could not be loaded.'
      }}
    </p>
    <div class="flex flex-wrap gap-3">
      <Button
        v-if="analysis.analysisQuery.isError.value"
        type="button"
        variant="outline"
        @click="analysis.analysisQuery.refetch()"
        ><RefreshCw :size="15" aria-hidden="true" /> Retry analysis load</Button
      ><Button type="button" variant="outline" @click="analysis.analyze">Analyze again</Button
      ><Button
        v-if="analysis.analysisConflict.value"
        type="button"
        variant="outline"
        @click="analysis.reloadCurrentRevision"
        >Reload current revision</Button
      >
    </div>
  </Card>
  <Card v-if="analysis.analysis.value" class="space-y-4" aria-live="polite">
    <div>
      <h2 class="text-lg font-bold text-text">Deterministic analysis</h2>
      <p class="text-xs text-text-muted">
        Analysis rule {{ analysis.analysis.value.analysis_rule_version }} · revision
        {{ analysis.analysis.value.job_description_revision_id }} · Analysis ID
        {{ analysis.analysis.value.id }}
      </p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
      <div
        v-for="(signal, name) in analysis.analysis.value.signals"
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
    <JdMatchCreator :match="match" />
  </Card>
</template>
