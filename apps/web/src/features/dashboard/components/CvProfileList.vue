<script setup lang="ts">
import { ArrowRight, Clock, Edit3, Eye, FileText } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import Button from '@/shared/components/ui/button/Button.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import SkeletonCard from '@/shared/components/molecules/SkeletonCard.vue'
import { ROUTES } from '@/shared/constants/routes'
import type { DashboardController } from '../composables/useDashboardController'
defineProps<{ controller: DashboardController }>()
</script>
<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between"><div><h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Active CV Profiles</h2><p class="text-xs sm:text-sm text-text-muted mt-0.5">Click any profile to modify sections or preview export.</p></div><RouterLink :to="ROUTES.CV_EDIT('new')"><span class="text-xs sm:text-sm font-semibold text-primary hover:text-primary-hover flex items-center gap-1.5 transition-colors">Create another <ArrowRight :size="13" /></span></RouterLink></div>
    <SkeletonCard v-if="controller.isLoading.value" :rows="3" />
    <Card v-else-if="controller.isError.value"><EmptyState title="Unable to load profiles" description="There was a connection issue loading CV data." action-label="Retry" @action="controller.refetch()" /></Card>
    <Card v-else-if="!controller.cvProfiles.value?.length"><EmptyState title="No CV Profiles yet" description="Create a Profile to start building your trusted CV source." /></Card>
    <div v-else class="space-y-3">
      <div v-for="profile in controller.cvProfiles.value" :key="profile.id" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 p-4 sm:p-5 bg-white border border-border rounded-xl hover:border-primary-border hover:shadow-2xs transition-all group">
        <div class="flex items-start sm:items-center gap-3.5 min-w-0"><div class="w-10 h-10 rounded-xl bg-primary-muted border border-primary-border/50 flex items-center justify-center flex-shrink-0 text-primary"><FileText :size="19" :stroke-width="1.5" /></div><div class="min-w-0"><div class="flex items-center gap-2"><p class="text-sm sm:text-base font-bold text-text truncate">{{ profile.title }}</p><span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-md bg-surface-muted text-text-quiet border border-border">Revision {{ profile.revision }}</span></div><div class="flex items-center gap-2.5 mt-1 text-xs text-text-muted"><span class="flex items-center gap-1"><Clock :size="13" /> Updated {{ new Date(profile.updated_at).toLocaleDateString() }}</span><span class="w-1 h-1 rounded-full bg-border-hover" /><span class="text-success-hover font-semibold">Profile source</span></div></div></div>
        <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-auto"><RouterLink :to="ROUTES.CV_PREVIEW(profile.id)"><Button size="sm" variant="outline"><Eye :size="13" /><span>Preview</span></Button></RouterLink><RouterLink :to="ROUTES.CV_EDIT(profile.id)"><Button size="sm"><Edit3 :size="13" /><span>Edit</span></Button></RouterLink></div>
      </div>
    </div>
  </section>
</template>
