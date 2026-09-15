<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Plus, FileText, Briefcase, BarChart2, ArrowRight } from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import SkeletonCard from '@/shared/components/molecules/SkeletonCard.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { useCvProfilesQuery } from '@/features/cv'

const { data: cvProfiles, isLoading, isError, refetch } = useCvProfilesQuery()

// Stat cards
const stats = [
  { icon: FileText, label: 'CV Profiles', value: '-', color: '#6366f1', bg: '#eef2ff' },
  { icon: Briefcase, label: 'Job Descriptions', value: '-', color: '#0ea5e9', bg: '#e0f2fe' },
  { icon: BarChart2, label: 'Match Reports', value: '-', color: '#10b981', bg: '#ecfdf5' },
]
</script>

<template>
  <div class="space-y-8">
    <!-- Page header -->
    <SectionHeader
      title="Dashboard"
      description="Your CV workspace — profiles, jobs, and match results."
      level="h1"
    >
      <template #action>
        <RouterLink :to="ROUTES.CV_EDIT('new')">
          <AppButton size="sm">
            <Plus :size="15" :stroke-width="1.5" />
            New CV
          </AppButton>
        </RouterLink>
      </template>
    </SectionHeader>

    <!-- Stats row — information-dense, not padded cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="flex items-center gap-3 px-4 py-3 bg-white border border-[#e2e8f0] rounded-lg"
      >
        <div
          class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
          :style="{ background: stat.bg }"
        >
          <component :is="stat.icon" :size="17" :stroke-width="1.5" :style="{ color: stat.color }" />
        </div>
        <div>
          <p class="text-xl font-semibold text-[#0f172a] leading-none">{{ stat.value }}</p>
          <p class="text-xs text-[#64748b] mt-0.5">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Quick actions strip -->
    <div class="flex flex-wrap gap-3">
      <RouterLink :to="ROUTES.JD_NEW">
        <AppButton variant="outline" size="sm">
          <Briefcase :size="14" :stroke-width="1.5" />
          Paste a job description
        </AppButton>
      </RouterLink>
      <RouterLink :to="ROUTES.TEMPLATES">
        <AppButton variant="outline" size="sm">
          Pick a template
        </AppButton>
      </RouterLink>
    </div>

    <!-- CV Profiles list -->
    <div>
      <SectionHeader
        title="CV Profiles"
        description="Base CVs you have saved."
        level="h2"
      >
        <template #action>
          <RouterLink :to="ROUTES.CV_EDIT('new')" class="text-sm text-[#6366f1] hover:text-[#4f46e5] font-medium transition-colors flex items-center gap-1">
            View all
            <ArrowRight :size="13" :stroke-width="1.5" />
          </RouterLink>
        </template>
      </SectionHeader>

      <div class="mt-4">
        <!-- Loading -->
        <SkeletonCard v-if="isLoading" :rows="3" />

        <!-- Error -->
        <Card v-else-if="isError" class="mt-4">
          <EmptyState
            title="Could not load CV profiles"
            description="Check your connection or try refreshing the page."
            action-label="Refresh"
            @action="() => refetch()"
          />
        </Card>

        <!-- Empty -->
        <EmptyState
          v-else-if="!cvProfiles?.length"
          :icon="FileText"
          title="No CV profiles yet"
          description="Create your first profile to get started."
          action-label="Create profile"
          @action="() => $router.push(ROUTES.CV_EDIT('new'))"
        />

        <!-- List -->
        <div v-else class="space-y-2 mt-4">
          <div
            v-for="profile in cvProfiles"
            :key="profile.id"
            class="flex items-center gap-3 px-4 py-3.5 bg-white border border-[#e2e8f0] rounded-lg hover:border-[#c7d2fe] transition-colors group"
          >
            <div class="w-9 h-9 rounded-lg bg-[#eef2ff] flex items-center justify-center flex-shrink-0">
              <FileText :size="16" :stroke-width="1.5" class="text-[#6366f1]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-[#0f172a] truncate">{{ profile.title }}</p>
              <p class="text-xs text-[#64748b]">Updated {{ new Date(profile.updated_at).toLocaleDateString() }}</p>
            </div>
            <RouterLink
              :to="ROUTES.CV_EDIT(profile.id)"
              class="opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <AppButton size="sm" variant="outline">Edit</AppButton>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
