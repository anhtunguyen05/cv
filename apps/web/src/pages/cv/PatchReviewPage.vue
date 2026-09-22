<script setup lang="ts">
import { ref } from 'vue'
import { GitCompare, ArrowLeft, Check, X, Sparkles, ArrowRight } from 'lucide-vue-next'
import { RouterLink, useRoute } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'

const route = useRoute()
const cvId = (route.params.id as string) ?? '1'

const patches = ref([
  {
    id: 1,
    section: 'Key Projects · CareerFitCV',
    reason: 'JD explicitly demands "Pinia state management" keyword in production features.',
    scoreImpact: '+6 pts',
    original: 'Engineered responsive single-page application with caching and client stores.',
    proposed:
      'Engineered responsive single-page application with TanStack Vue Query caching and Pinia client state management.',
    status: 'pending' as 'pending' | 'accepted' | 'rejected',
  },
  {
    id: 2,
    section: 'Technical Skills · Tools',
    reason: 'Adds missing Vitest keyword grounded in repository testing evidence.',
    scoreImpact: '+5 pts',
    original: 'Git, Docker, Postman, Figma',
    proposed: 'Git, Docker, Vitest (Unit Testing), Postman, Figma',
    status: 'pending' as 'pending' | 'accepted' | 'rejected',
  },
])

function acceptPatch(id: number) {
  const p = patches.value.find((x) => x.id === id)
  if (p) p.status = 'accepted'
}

function rejectPatch(id: number) {
  const p = patches.value.find((x) => x.id === id)
  if (p) p.status = 'rejected'
}
</script>

<template>
  <div class="space-y-8 max-w-4xl">
    <!-- Header -->
    <div class="space-y-3 pb-4 border-b border-border/80">
      <RouterLink
        :to="ROUTES.CV_EDIT(cvId)"
        class="inline-flex items-center gap-2 text-sm font-medium text-text-muted hover:text-text transition-colors"
      >
        <ArrowLeft :size="16" />
        <span>Return to CV Editor</span>
      </RouterLink>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <div>
          <div class="flex items-center gap-2.5 mb-1.5">
            <span class="text-xs font-bold text-primary uppercase tracking-wider"
              >Selective AI Patches</span
            >
            <span class="text-xs text-border-hover">•</span>
            <span class="text-xs font-semibold text-text-muted">Human in the Loop</span>
          </div>
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-text">
            Review Proposed Improvements
          </h1>
          <p class="text-sm sm:text-base text-text-muted mt-1.5 leading-relaxed">
            Review and selectively accept verified diffs without overwriting your master profile.
          </p>
        </div>

        <RouterLink :to="`/cv/${cvId}/preview`">
          <AppButton size="md">
            <span>Preview Document</span>
            <ArrowRight :size="15" />
          </AppButton>
        </RouterLink>
      </div>
    </div>

    <!-- Patches List -->
    <div class="space-y-6">
      <Card v-for="patch in patches" :key="patch.id" class="space-y-5 border border-border">
        <!-- Patch meta header -->
        <div
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-surface-muted"
        >
          <div class="flex items-center gap-3">
            <GitCompare :size="18" class="text-primary" />
            <span class="text-base font-bold text-text">{{ patch.section }}</span>
            <AppBadge variant="primary" :label="patch.scoreImpact" />
          </div>

          <div class="flex items-center gap-2.5">
            <span
              v-if="patch.status === 'accepted'"
              class="text-xs font-bold text-success-text bg-success-muted border border-success-border px-3 py-1 rounded-full flex items-center gap-1.5"
            >
              <Check :size="14" />
              Accepted
            </span>
            <span
              v-else-if="patch.status === 'rejected'"
              class="text-xs font-bold text-danger-text bg-danger-muted border border-danger-border px-3 py-1 rounded-full flex items-center gap-1.5"
            >
              <X :size="14" />
              Declined
            </span>
            <template v-else>
              <button
                type="button"
                class="px-3.5 py-2 rounded-xl text-sm font-medium text-text-quiet hover:bg-surface-muted hover:text-text transition-colors cursor-pointer"
                @click="rejectPatch(patch.id)"
              >
                Decline
              </button>
              <AppButton size="sm" @click="acceptPatch(patch.id)">
                <Check :size="14" />
                <span>Accept Patch</span>
              </AppButton>
            </template>
          </div>
        </div>

        <!-- Diff view -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm font-mono">
          <!-- Current content -->
          <div
            class="p-4 sm:p-5 rounded-2xl bg-danger-muted/70 border border-danger-border space-y-1.5"
          >
            <div class="text-xs font-bold text-danger-text uppercase tracking-wider">
              Original Text
            </div>
            <p class="text-danger-strong leading-relaxed">{{ patch.original }}</p>
          </div>

          <!-- Proposed content -->
          <div
            class="p-4 sm:p-5 rounded-2xl bg-success-muted/80 border border-success-border space-y-1.5"
          >
            <div class="text-xs font-bold text-success-text uppercase tracking-wider">
              Proposed ATS Replacement
            </div>
            <p class="text-success-strong leading-relaxed">{{ patch.proposed }}</p>
          </div>
        </div>

        <!-- Rationale -->
        <div class="flex items-center gap-2.5 text-sm text-text-muted pt-1">
          <Sparkles :size="16" class="text-primary flex-shrink-0" />
          <span>Rationale: {{ patch.reason }}</span>
        </div>
      </Card>
    </div>
  </div>
</template>
