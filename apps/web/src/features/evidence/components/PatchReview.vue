<script setup lang="ts">
import { ArrowLeft, Check, RefreshCw, X } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import Button from '@/shared/components/ui/button/Button.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { usePatchReviewController } from '@/features/evidence/composables/usePatchReviewController'

const {
  patchId,
  legacyCvId,
  draft,
  query,
  edit,
  reject,
  approve,
  regenerate,
  confirmDecision,
  isRetryable,
  startEdit,
  runPatchMutation,
  sourceValue,
} = usePatchReviewController()
</script>

<template>
  <div class="space-y-6 max-w-4xl mx-auto" aria-live="polite">
    <div class="space-y-3 pb-4 border-b border-border/80">
      <RouterLink
        :to="
          query.data.value
            ? ROUTES.AI_INTERVIEW(query.data.value.interview_id)
            : legacyCvId
              ? ROUTES.CV_EDIT(legacyCvId)
              : ROUTES.MATCH_REPORTS
        "
        class="inline-flex items-center gap-2 text-sm font-medium text-text-muted hover:text-text"
      >
        <ArrowLeft :size="16" aria-hidden="true" />
        <span>Return to source</span>
      </RouterLink>
      <p class="text-xs font-bold uppercase tracking-wider text-primary">
        Evidence-based AI revision
      </p>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
        Review proposed improvement
      </h1>
      <p class="text-sm text-text-muted">
        The current CV source, User Evidence, and provider proposal remain separate. Nothing is
        applied until you explicitly approve it.
      </p>
    </div>

    <Card v-if="query.isLoading.value" class="space-y-3" aria-label="Loading Patch proposal">
      <div class="h-6 w-48 animate-pulse rounded bg-surface-muted" />
      <div class="h-28 animate-pulse rounded bg-surface-muted" />
    </Card>
    <Card v-else-if="query.isError.value" class="space-y-3" role="alert">
      <h2 class="text-lg font-bold text-text">Unable to load this proposal</h2>
      <p class="text-sm text-text-muted">
        {{ query.error.value instanceof Error ? query.error.value.message : 'The proposal could not be loaded.' }}
      </p>
      <Button
        v-if="isRetryable(query.error.value)"
        type="button"
        variant="outline"
        @click="query.refetch()"
      >
        <RefreshCw :size="15" aria-hidden="true" /> Retry
      </Button>
      <RouterLink
        v-else
        :to="ROUTES.MATCH_REPORTS"
        class="text-sm font-semibold text-primary hover:underline"
        >Return to Match Reports</RouterLink
      >
    </Card>
    <Card v-else-if="!patchId" class="space-y-3" role="status">
      <h2 class="text-lg font-bold text-text">Choose a Patch proposal</h2>
      <p class="text-sm text-text-muted">
        Open a server-created proposal from an Evidence interview to review it here.
      </p>
      <RouterLink
        :to="ROUTES.MATCH_REPORTS"
        class="text-sm font-semibold text-primary hover:underline"
        >Return to Match Reports</RouterLink
      >
    </Card>
    <template v-else-if="query.data.value">
      <Card class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-xs text-text-muted">Patch revision {{ query.data.value.revision }}</p>
            <h2 class="text-lg font-bold text-text">
              {{ query.data.value.target.section }} · {{ query.data.value.target.field }}
            </h2>
          </div>
          <AppBadge
            :label="query.data.value.status"
            :variant="query.data.value.status === 'pending' ? 'warning' : 'muted'"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div class="rounded-xl border border-danger-border bg-danger-muted/60 p-4 space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-danger-text">
              Current CV source
            </h3>
            <p class="whitespace-pre-wrap text-danger-strong">
              {{ sourceValue(query.data.value.old_value) }}
            </p>
          </div>
          <div class="rounded-xl border border-success-border bg-success-muted/60 p-4 space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-success-text">
              Provider proposal
            </h3>
            <p class="whitespace-pre-wrap text-success-strong">{{ query.data.value.new_value }}</p>
          </div>
        </div>
        <p class="text-sm text-text-muted">Reason: {{ query.data.value.reason }}</p>

        <div
          v-if="query.data.value.evidence?.length"
          class="rounded-xl border border-border bg-surface-muted/40 p-4 space-y-2"
        >
          <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">User Evidence</h3>
          <p
            v-for="evidence in query.data.value.evidence"
            :key="evidence.id"
            class="text-sm text-text"
          >
            {{ evidence.outcome === 'answer' ? evidence.answer : 'Cannot provide evidence' }}
          </p>
        </div>

        <div
          v-if="query.data.value.allowed_actions.includes('edit')"
          class="border-t border-border pt-4 space-y-3"
        >
          <label for="patch-edit" class="text-sm font-semibold text-text"
            >Edit the proposed value (optional)</label
          >
          <textarea
            id="patch-edit"
            v-model="draft"
            rows="4"
            class="w-full rounded-xl border border-border p-3 text-sm"
            @focus="startEdit"
          />
          <div class="flex flex-wrap gap-2">
            <Button
              type="button"
              variant="outline"
              :disabled="edit.isPending.value"
              @click="runPatchMutation('edit')"
              >Save edit</Button
            >
            <Button
              type="button"
              variant="outline"
              :disabled="reject.isPending.value"
              @click="
                confirmDecision(
                  'Reject this Patch proposal? It will remain available for lineage-linked regeneration.',
                  () => runPatchMutation('reject'),
                )
              "
              ><X :size="15" aria-hidden="true" /> Reject</Button
            >
            <Button
              type="button"
              :loading="approve.isPending.value"
              :disabled="approve.isPending.value"
              @click="
                confirmDecision('Approve this Patch into one new immutable CV Version?', () =>
                  runPatchMutation('approve'),
                )
              "
              ><Check :size="15" aria-hidden="true" /> Approve into new CV Version</Button
            >
          </div>
          <p class="text-xs text-text-muted">
            Approval is an explicit action and creates one immutable CV Version.
          </p>
        </div>
        <div
          v-else-if="query.data.value.allowed_actions.includes('regenerate')"
          class="border-t border-border pt-4 space-y-3"
        >
          <p class="text-sm text-text-muted">
            This Patch is {{ query.data.value.status }}. You can request a predecessor-linked
            regeneration.
          </p>
          <Button
            type="button"
            :loading="regenerate.isPending.value"
            :disabled="regenerate.isPending.value"
            @click="runPatchMutation('regenerate')"
            >Regenerate proposal</Button
          >
        </div>
        <p
          v-else-if="query.data.value.status === 'pending_validation'"
          class="border-t border-border pt-4 text-sm text-text-muted"
          role="status"
        >
          This proposal is still being validated. Refresh shortly for the next server-owned action.
        </p>
        <p
          v-if="edit.isError.value || reject.isError.value || approve.isError.value || regenerate.isError.value"
          class="text-sm text-danger-text"
          role="alert"
        >
          This action could not be committed. Refresh the proposal and retry if it is still
          available.
        </p>
        <p
          v-if="query.data.value.status === 'applied'"
          class="text-sm text-success-text"
          role="status"
        >
          Applied to immutable CV Version
          <RouterLink
            v-if="query.data.value.applied_version_id"
            :to="ROUTES.CV_VERSION_PREVIEW(query.data.value.applied_version_id)"
            class="font-semibold underline"
            >{{ query.data.value.applied_version_id }}</RouterLink
          >.
        </p>
      </Card>
    </template>
  </div>
</template>
