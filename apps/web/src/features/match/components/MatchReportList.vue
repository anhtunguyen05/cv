<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useMatchReportsQuery } from '@/features/match/api/match.queries'
import Card from '@/shared/components/ui/card/Card.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'
import { RefreshCw } from 'lucide-vue-next'

const page = ref(1)
const perPage = 20
const reportsQuery = useMatchReportsQuery(page, perPage)
const pageNavigation = usePageNavigation({
  page,
  lastPage: () => reportsQuery.data.value?.lastPage,
  isFetching: reportsQuery.isFetching,
})

const retryable = (error: unknown) =>
  error instanceof ApiRequestError && [429, 503].includes(error.status)
</script>

<template>
  <div class="space-y-6" aria-live="polite">
    <div class="space-y-2 pb-4 border-b border-border/80">
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">Match Reports</h1>
      <p class="text-xs sm:text-sm text-text-muted">
        Historical, source-pinned comparisons between your CV Versions and Job Description
        revisions.
      </p>
    </div>

    <Card v-if="reportsQuery.isLoading.value" aria-label="Loading Match Reports">
      <div class="h-32 animate-pulse rounded bg-surface-muted" />
    </Card>
    <Card v-else-if="reportsQuery.isError.value" role="alert" class="space-y-3">
      <h2 class="text-lg font-bold text-text">Unable to load Match Reports</h2>
      <p class="text-sm text-text-muted">
        {{
          reportsQuery.error.value instanceof Error
            ? reportsQuery.error.value.message
            : 'The reports could not be loaded.'
        }}
      </p>
      <Button
        v-if="retryable(reportsQuery.error.value)"
        type="button"
        variant="outline"
        @click="reportsQuery.refetch()"
      >
        <RefreshCw :size="15" aria-hidden="true" /> Retry
      </Button>
    </Card>
    <Card v-else-if="!reportsQuery.data.value?.items.length" class="space-y-3">
      <h2 class="text-lg font-bold text-text">No Match Reports yet</h2>
      <p class="text-sm text-text-muted">
        Analyze a saved Job Description and choose a CV Version to create your first report.
      </p>
      <RouterLink
        :to="ROUTES.JD_NEW"
        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white"
        >Analyze a Job Description</RouterLink
      >
    </Card>
    <div v-else class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <RouterLink
          v-for="report in reportsQuery.data.value?.items ?? []"
          :key="report.id"
          :to="ROUTES.MATCH_REPORT(report.id)"
          class="block"
        >
          <Card class="h-full space-y-3 transition hover:border-primary/50 hover:shadow-card-quiet">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h2 class="font-bold text-text">
                  {{ report.source_summary.role || 'Match Report' }}
                </h2>
                <p class="text-xs text-text-muted">
                  {{ report.source_summary.company || 'Saved Job Description' }}
                </p>
              </div>
              <span class="text-2xl font-bold text-primary"
                >{{ report.overall_score.toFixed(1) }}%</span
              >
            </div>
            <p class="text-xs text-text-muted">
              CV {{ report.cv_version_id }} · revision
              {{ report.source_summary.revision_number ?? 'deleted' }}
            </p>
            <p class="text-xs text-text-secondary">
              {{ report.matched_skills.length }} matched ·
              {{ report.missing_skills.length }} missing · {{ report.weak_evidence.length }} weak
              evidence
            </p>
          </Card>
        </RouterLink>
      </div>
      <PaginationNav
        :page="pageNavigation.page.value"
        :last-page="pageNavigation.lastPage.value"
        label="Match Report pages"
        @previous="pageNavigation.previous"
        @next="pageNavigation.next"
      />
    </div>
  </div>
</template>
