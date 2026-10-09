<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import Card from '@/shared/components/ui/card/Card.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import SkeletonCard from '@/shared/components/molecules/SkeletonCard.vue'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import { ROUTES } from '@/shared/constants/routes'
import type { DashboardJobDescriptionsContract } from '../composables/useDashboardController'
defineProps<{ jobDescriptions: DashboardJobDescriptionsContract }>()
</script>
<template>
  <section class="space-y-4 pt-2">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">
          Saved Job Descriptions
        </h2>
        <p class="text-xs sm:text-sm text-text-muted mt-0.5">
          Reload a source to edit, analyze, or remove it.
        </p>
      </div>
      <RouterLink :to="ROUTES.JD_NEW"
        ><span
          class="text-xs sm:text-sm font-semibold text-primary hover:text-primary-hover flex items-center gap-1.5 transition-colors"
          >Save another <ArrowRight :size="13" /></span
      ></RouterLink>
    </div>
    <SkeletonCard v-if="jobDescriptions.isLoading.value" :rows="2" />
    <Card v-else-if="jobDescriptions.isError.value"
      ><EmptyState
        title="Unable to load saved Job Descriptions"
        description="There was a connection issue loading saved sources."
        action-label="Retry"
        @action="jobDescriptions.refetch()"
    /></Card>
    <Card v-else-if="!jobDescriptions.items.value?.length"
      ><EmptyState
        title="No saved Job Descriptions"
        description="Save a source to start a deterministic analysis."
    /></Card>
    <div v-else class="space-y-3">
      <RouterLink
        v-for="job in jobDescriptions.items.value"
        :key="job.id"
        :to="ROUTES.JD_DETAIL(job.id)"
        class="flex items-center justify-between gap-4 p-4 bg-white border border-border rounded-xl hover:border-primary-border hover:shadow-2xs transition-all"
        ><div class="min-w-0">
          <p class="text-sm font-bold text-text truncate">{{ job.role || 'Untitled role' }}</p>
          <p class="text-xs text-text-muted mt-1 truncate">
            {{ job.company || 'Company not specified' }} · Revision
            {{ job.current_revision?.revision_number || 0 }}
          </p>
        </div>
        <span class="text-xs text-text-muted whitespace-nowrap">{{
          new Date(job.updated_at).toLocaleDateString()
        }}</span></RouterLink
      >
      <PaginationNav
        :page="jobDescriptions.navigation.page.value"
        :last-page="jobDescriptions.navigation.lastPage.value"
        label="Saved Job Description pages"
        @previous="jobDescriptions.navigation.previous"
        @next="jobDescriptions.navigation.next"
      />
    </div>
  </section>
</template>
