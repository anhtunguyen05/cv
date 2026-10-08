<script setup lang="ts">
import { RouterLink } from 'vue-router'
import MatchScoreRing from '@/features/match/components/MatchScoreRing.vue'
import SkillGapList from '@/features/match/components/SkillGapList.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import { ArrowLeft, RefreshCw, ShieldCheck } from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import {
  matchPriorityLabels as priorityLabels,
  useMatchReportController,
} from '@/features/match/composables/useMatchReportController'

const {
  reportQuery,
  retryable,
  unresolvedAreaCount,
  interviewAreaCount,
  interviewMutation,
  interviewError,
  interviewNotNeeded,
  interviewConflict,
  conflictInterviewId,
  startInterview,
  recoverInterviewConflict,
} = useMatchReportController()
</script>

<template>
  <div class="space-y-6" aria-live="polite">
    <div class="space-y-2.5 pb-4 border-b border-border/80">
      <RouterLink
        :to="ROUTES.DASHBOARD"
        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-text-muted hover:text-text"
      >
        <ArrowLeft :size="15" aria-hidden="true" />
        <span>Back to Dashboard</span>
      </RouterLink>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">Match Report</h1>
      <p class="text-xs sm:text-sm text-text-muted">
        A stored, source-pinned comparison between one CV Version and one Job Description revision.
      </p>
    </div>

    <section v-if="reportQuery.isLoading.value" class="space-y-4" aria-label="Loading match report">
      <Card>
        <div class="h-6 w-48 animate-pulse rounded bg-surface-muted" />
        <div class="mt-4 h-28 animate-pulse rounded bg-surface-muted" />
      </Card>
    </section>

    <Card v-else-if="reportQuery.isError.value" role="alert" class="space-y-3">
      <h2 class="text-lg font-bold text-text">Unable to load this report</h2>
      <p class="text-sm text-text-muted">
        {{
          reportQuery.error.value instanceof Error
            ? reportQuery.error.value.message
            : 'The report could not be loaded.'
        }}
      </p>
      <Button v-if="retryable" type="button" variant="outline" @click="reportQuery.refetch()">
        <RefreshCw :size="15" aria-hidden="true" /> Retry
      </Button>
      <p v-else class="text-xs text-text-muted">
        The report may not exist or may not belong to this account.
      </p>
    </Card>

    <template v-else-if="reportQuery.data.value">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
        <div class="lg:col-span-4 space-y-5">
          <Card class="bg-gradient-to-b from-white to-surface space-y-4">
            <MatchScoreRing :score="reportQuery.data.value.overall_score" />
            <p class="text-xs text-text-muted text-center">
              Score is advisory and based on the pinned rule version
              {{ reportQuery.data.value.matching_rule_version }}.
            </p>
          </Card>
          <Card class="space-y-3">
            <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider">
              Pinned sources
            </h2>
            <dl class="space-y-2 text-xs text-text-secondary">
              <div class="flex justify-between gap-3">
                <dt>CV Version</dt>
                <dd class="font-mono">{{ reportQuery.data.value.cv_version_id }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>Job Description</dt>
                <dd class="font-mono">{{ reportQuery.data.value.job_description_id }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>JD revision</dt>
                <dd class="font-mono">{{ reportQuery.data.value.job_description_revision_id }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>Analysis</dt>
                <dd class="font-mono">{{ reportQuery.data.value.analysis_id }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>Role / company</dt>
                <dd>
                  {{ reportQuery.data.value.source_summary.role || 'Untitled' }} ·
                  {{ reportQuery.data.value.source_summary.company || 'Not specified' }}
                </dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>Analysis rule</dt>
                <dd>{{ reportQuery.data.value.analysis_rule_version }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt>Source status</dt>
                <dd>
                  {{
                    reportQuery.data.value.source_summary.source_deleted
                      ? 'Deleted source; historical report'
                      : reportQuery.data.value.source_summary.source_is_current
                        ? 'Current source'
                        : 'Pinned historical revision'
                  }}
                </dd>
              </div>
            </dl>
          </Card>
        </div>

        <div class="lg:col-span-8 space-y-6">
          <Card class="space-y-4">
            <div>
              <h2 class="text-lg sm:text-xl font-bold text-text">Evidence groups</h2>
              <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                Matched, missing, and weak evidence are shown with the references used by the stored
                report.
              </p>
            </div>
            <SkillGapList
              :matched="reportQuery.data.value.matched_skills"
              :missing="reportQuery.data.value.missing_skills"
              :weak="reportQuery.data.value.weak_evidence"
            />
          </Card>

          <Card class="space-y-4" aria-labelledby="interview-cta-title">
            <div class="flex items-start gap-3">
              <ShieldCheck
                :size="20"
                class="text-primary mt-0.5 flex-shrink-0"
                aria-hidden="true"
              />
              <div class="space-y-1">
                <h2 id="interview-cta-title" class="text-lg sm:text-xl font-bold text-text">
                  Strengthen your evidence
                </h2>
                <p class="text-xs sm:text-sm text-text-muted">
                  Answer server-selected questions about the unresolved areas in this stored report.
                  Your CV Version stays unchanged.
                </p>
              </div>
            </div>

            <div v-if="unresolvedAreaCount > 0 && !interviewNotNeeded" class="space-y-3">
              <p class="text-sm text-text-secondary">
                {{ unresolvedAreaCount }} unresolved area{{ unresolvedAreaCount === 1 ? '' : 's' }}
                found; the first {{ interviewAreaCount }} will be included, with one question per
                area.
              </p>
              <Button
                type="button"
                :disabled="interviewMutation.isPending.value"
                :aria-busy="interviewMutation.isPending.value"
                @click="startInterview"
              >
                {{
                  interviewMutation.isPending.value
                    ? 'Starting interview…'
                    : 'Start Evidence interview'
                }}
              </Button>
            </div>

            <div
              v-else-if="unresolvedAreaCount === 0 || interviewNotNeeded"
              class="rounded-xl border border-dashed border-border p-4 text-sm text-text-muted"
              data-testid="interview-not-needed"
              role="status"
            >
              No interview is needed for this report because it has no unresolved evidence areas.
            </div>

            <div
              v-if="interviewMutation.isError.value && !interviewNotNeeded"
              class="space-y-3"
              role="alert"
            >
              <p class="text-sm text-danger-text">
                {{
                  interviewConflict
                    ? 'This interview is no longer available. Refresh the report to see the current state.'
                    : interviewError instanceof Error
                      ? interviewError.message
                      : 'The interview could not be started.'
                }}
              </p>
              <Button
                type="button"
                variant="outline"
                :disabled="reportQuery.isFetching.value"
                @click="recoverInterviewConflict"
              >
                <RefreshCw :size="15" aria-hidden="true" />
                {{ conflictInterviewId ? 'Open current interview' : 'Refresh report' }}
              </Button>
            </div>
          </Card>

          <Card class="space-y-4">
            <div>
              <h2 class="text-lg sm:text-xl font-bold text-text">Advisory recommendations</h2>
              <p class="text-xs sm:text-sm text-text-muted">
                Recommendations are proposals for truthful CV edits and do not promise a score
                change.
              </p>
            </div>
            <div
              v-if="!reportQuery.data.value.recommendations.length"
              class="text-sm text-text-muted"
            >
              No recommendation was stored for this report.
            </div>
            <div
              v-for="rec in reportQuery.data.value.recommendations"
              :key="rec.id"
              class="p-4 rounded-xl border border-border bg-white space-y-2"
            >
              <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-bold text-text">{{ rec.target_cv_section }}</span>
                <AppBadge
                  :label="priorityLabels[rec.priority]"
                  :variant="
                    rec.priority === 'high'
                      ? 'danger'
                      : rec.priority === 'medium'
                        ? 'warning'
                        : 'muted'
                  "
                  size="sm"
                />
              </div>
              <p class="text-xs sm:text-sm text-text-secondary">{{ rec.rationale }}</p>
              <p class="text-xs text-text-muted">
                Related signals:
                <span class="font-mono">{{ rec.related_signal_ids.join(', ') || 'none' }}</span>
              </p>
              <p class="text-xs text-text-muted">{{ rec.action }}</p>
            </div>
          </Card>
        </div>
      </div>
    </template>
  </div>
</template>
