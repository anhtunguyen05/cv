<script setup lang="ts">
import { BarChart2, Briefcase, FileText, TrendingUp } from 'lucide-vue-next'
import Card from '@/shared/components/ui/card/Card.vue'
import type { DashboardStatsContract } from '../composables/useDashboardController'
defineProps<{ stats: DashboardStatsContract }>()
</script>
<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
    <Card
      padding="sm"
      class="relative overflow-hidden group hover:border-primary-border transition-all"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">CV Profiles</span>
        <div
          class="w-9 h-9 rounded-lg bg-primary-muted flex items-center justify-center text-primary"
        >
          <FileText :size="18" :stroke-width="1.5" />
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">{{
          stats.cvProfileTotal.value
        }}</span
        ><span
          class="text-xs font-semibold text-success-hover bg-success-muted border border-success-border px-2 py-0.5 rounded-full flex items-center gap-1"
          ><TrendingUp :size="12" /> Active</span
        >
      </div>
      <p class="text-xs text-text-muted mt-1.5 leading-relaxed">
        Master profile &amp; 1 tailored snapshot
      </p>
    </Card>
    <Card
      padding="sm"
      class="relative overflow-hidden group hover:border-info-border transition-all"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider"
          >Saved Job Descriptions</span
        >
        <div class="w-9 h-9 rounded-lg bg-info-muted flex items-center justify-center text-info">
          <Briefcase :size="18" :stroke-width="1.5" />
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">{{
          stats.jobDescriptionTotal.value
        }}</span
        ><span
          class="text-xs font-semibold text-info bg-info-muted border border-info-border px-2 py-0.5 rounded-full"
          >Saved</span
        >
      </div>
      <p class="text-xs text-text-muted mt-1.5 leading-relaxed">
        Open a saved source to edit or analyze it.
      </p>
    </Card>
    <Card
      padding="sm"
      class="relative overflow-hidden group hover:border-success-border transition-all"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider"
          >Match Reports</span
        >
        <div
          class="w-9 h-9 rounded-lg bg-success-muted flex items-center justify-center text-success-hover"
        >
          <BarChart2 :size="18" :stroke-width="1.5" />
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">{{
          stats.matchReportTotal.value
        }}</span
        ><span
          class="text-xs font-semibold text-success-hover bg-success-muted border border-success-border px-2 py-0.5 rounded-full"
          >Stored</span
        >
      </div>
      <p class="text-xs text-text-muted mt-1.5 leading-relaxed">
        Reports retain the CV and Job Description sources used.
      </p>
      <p v-if="stats.isMatchReportsLoading.value" class="text-xs text-text-muted" role="status">
        Loading stored reports…
      </p>
      <p v-else-if="stats.isMatchReportsError.value" class="text-xs text-danger" role="alert">
        Unable to load report totals.
        <button type="button" class="underline" @click="stats.refetchMatchReports()">Retry</button>
      </p>
    </Card>
  </div>
</template>
