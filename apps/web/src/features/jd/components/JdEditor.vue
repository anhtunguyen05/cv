<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ArrowLeft, RefreshCw } from 'lucide-vue-next'
import Button from '@/shared/components/ui/button/Button.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { ROUTES } from '@/shared/constants/routes'
import { useJdEditorController } from '../composables/useJdEditorController'
import JdAnalysisPanel from './JdAnalysisPanel.vue'
import JdForm from './JdForm.vue'
const controller = useJdEditorController()
</script>
<template>
  <div class="space-y-6 sm:space-y-8" aria-live="polite">
    <div class="pb-4 border-b border-border/80">
      <RouterLink
        :to="ROUTES.DASHBOARD"
        class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-text"
        ><ArrowLeft :size="14" aria-hidden="true" /> Dashboard</RouterLink
      >
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text mt-3">
        {{ controller.page.current.value ? 'Edit Job Description' : 'Save Job Description' }}
      </h1>
      <p class="text-xs sm:text-sm text-text-muted mt-1">
        Preserve the source exactly as pasted, then run the deterministic analysis when you are
        ready.
      </p>
    </div>
    <Card v-if="controller.page.jdQuery.isLoading.value" aria-label="Loading Job Description"
      ><div class="h-40 animate-pulse rounded bg-surface-muted"
    /></Card>
    <Card v-else-if="controller.page.jdQuery.isError.value" role="alert" class="space-y-3"
      ><h2 class="font-bold text-text">Unable to load this Job Description</h2>
      <p class="text-sm text-text-muted">
        {{ controller.page.errorMessage.value || 'The resource was not found.' }}
      </p>
      <Button
        v-if="controller.page.retryable.value"
        variant="outline"
        type="button"
        @click="controller.page.jdQuery.refetch()"
        ><RefreshCw :size="15" aria-hidden="true" /> Retry</Button
      ></Card
    >
    <template v-else
      ><JdForm :form="controller.form" /><JdAnalysisPanel
        :analysis="controller.analysis"
        :match="controller.match"
    /></template>
  </div>
</template>
