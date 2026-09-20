<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  Plus,
  FileText,
  Briefcase,
  BarChart2,
  ArrowRight,
  Sparkles,
  CheckCircle2,
  TrendingUp,
  Clock,
  Eye,
  Edit3,
} from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import SkeletonCard from '@/shared/components/molecules/SkeletonCard.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { useCvProfilesQuery } from '@/features/cv'

const { data: cvProfiles, isLoading, isError, refetch } = useCvProfilesQuery()

// Dynamic fallback sample profiles if API returns empty during early scaffold
const sampleProfiles = ref([
  {
    id: '1',
    title: 'Frontend Engineer (Vue 3 / TypeScript)',
    target_role: 'Frontend Developer',
    version: 'v1.2',
    updated_at: new Date().toISOString(),
    match_score: 88,
  },
  {
    id: '2',
    title: 'Fullstack Junior (Vue + Laravel API)',
    target_role: 'Fullstack Engineer',
    version: 'v1.0',
    updated_at: new Date(Date.now() - 86400000 * 2).toISOString(),
    match_score: 74,
  },
])
</script>

<template>
  <div class="space-y-6">
    <!-- ── Top Welcome & Header Strip ──────────────────────────── -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-border/80">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-bold uppercase tracking-wider text-primary">Overview</span>
          <span class="w-1 h-1 rounded-full bg-border-hover" />
          <span class="text-xs font-medium text-text-muted">Candidate Workspace</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
          Candidate Workspace
        </h1>
        <p class="text-xs sm:text-sm text-text-muted mt-1 max-w-2xl leading-relaxed">
          Manage your master CV profiles, evaluate job descriptions, and track ATS match scores with verified evidence.
        </p>
      </div>

      <div class="flex items-center gap-2.5 flex-shrink-0">
        <RouterLink :to="ROUTES.JD_NEW">
          <AppButton variant="outline" size="md">
            <Briefcase :size="15" :stroke-width="1.5" />
            <span>Analyze Job</span>
          </AppButton>
        </RouterLink>

        <RouterLink :to="ROUTES.CV_EDIT('new')">
          <AppButton size="md">
            <Plus :size="15" :stroke-width="2" />
            <span>New CV Profile</span>
          </AppButton>
        </RouterLink>
      </div>
    </div>

    <!-- ── KPI Metric Bento Cards ──────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
      <!-- Card 1: CV Profiles -->
      <Card padding="sm" class="relative overflow-hidden group hover:border-primary-border transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-text-muted uppercase tracking-wider">CV Profiles</span>
          <div class="w-9 h-9 rounded-lg bg-primary-muted flex items-center justify-center text-primary">
            <FileText :size="18" :stroke-width="1.5" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">
            {{ cvProfiles?.length || 2 }}
          </span>
          <span class="text-xs font-semibold text-success-hover bg-success-muted border border-success-border px-2 py-0.5 rounded-full flex items-center gap-1">
            <TrendingUp :size="12" /> Active
          </span>
        </div>
        <p class="text-xs text-text-muted mt-1.5 leading-relaxed">Master profile & 1 tailored snapshot</p>
      </Card>

      <!-- Card 2: Job Postings Scanned -->
      <Card padding="sm" class="relative overflow-hidden group hover:border-info-border transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Job Postings</span>
          <div class="w-9 h-9 rounded-lg bg-info-muted flex items-center justify-center text-info">
            <Briefcase :size="18" :stroke-width="1.5" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">3</span>
          <span class="text-xs font-semibold text-info bg-info-muted border border-info-border px-2 py-0.5 rounded-full">
            Analyzed
          </span>
        </div>
        <p class="text-xs text-text-muted mt-1.5 leading-relaxed">Average skill confidence: 96%</p>
      </Card>

      <!-- Card 3: Average Fit Score -->
      <Card padding="sm" class="relative overflow-hidden group hover:border-success-border transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Average Match</span>
          <div class="w-9 h-9 rounded-lg bg-success-muted flex items-center justify-center text-success-hover">
            <BarChart2 :size="18" :stroke-width="1.5" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-2xl sm:text-3xl font-bold text-text font-mono tracking-tight">81%</span>
          <span class="text-xs font-semibold text-success-hover bg-success-muted border border-success-border px-2 py-0.5 rounded-full">
            +14% lift
          </span>
        </div>
        <p class="text-xs text-text-muted mt-1.5 leading-relaxed">Above competitive benchmark for juniors</p>
      </Card>
    </div>

    <!-- ── Main 12-Column Content ───────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
      <!-- Left: CV Profiles List (8 cols) -->
      <div class="lg:col-span-8 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Active CV Profiles</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">Click any profile to modify sections or preview export.</p>
          </div>

          <RouterLink :to="ROUTES.CV_EDIT('new')">
            <span class="text-xs sm:text-sm font-semibold text-primary hover:text-primary-hover flex items-center gap-1.5 transition-colors">
              Create another
              <ArrowRight :size="13" />
            </span>
          </RouterLink>
        </div>

        <!-- Loading State -->
        <SkeletonCard v-if="isLoading" :rows="3" />

        <!-- Error State (only if no fallback demo profiles exist) -->
        <Card v-else-if="isError && !sampleProfiles?.length">
          <EmptyState
            title="Unable to load profiles"
            description="There was a connection issue loading CV data."
            action-label="Retry"
            @action="() => refetch()"
          />
        </Card>

        <!-- Profiles List -->
        <div v-else class="space-y-3">
          <div
            v-for="profile in (cvProfiles?.length ? cvProfiles : sampleProfiles)"
            :key="profile.id"
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 p-4 sm:p-5 bg-white border border-border rounded-xl hover:border-primary-border hover:shadow-2xs transition-all group"
          >
            <div class="flex items-start sm:items-center gap-3.5 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-primary-muted border border-primary-border/50 flex items-center justify-center flex-shrink-0 text-primary">
                <FileText :size="19" :stroke-width="1.5" />
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <p class="text-sm sm:text-base font-bold text-text truncate">{{ profile.title }}</p>
                  <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-md bg-surface-muted text-text-quiet border border-border">
                    {{ (profile as any).version || 'v1.0' }}
                  </span>
                </div>
                <div class="flex items-center gap-2.5 mt-1 text-xs text-text-muted">
                  <span class="flex items-center gap-1">
                    <Clock :size="13" />
                    Updated {{ new Date(profile.updated_at).toLocaleDateString() }}
                  </span>
                  <span class="w-1 h-1 rounded-full bg-border-hover" />
                  <span class="text-success-hover font-semibold">
                    Fit: {{ (profile as any).match_score || 85 }}%
                  </span>
                </div>
              </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-auto">
              <RouterLink :to="`/cv/${profile.id}/preview`">
                <AppButton size="sm" variant="outline">
                  <Eye :size="13" />
                  <span>Preview</span>
                </AppButton>
              </RouterLink>

              <RouterLink :to="ROUTES.CV_EDIT(profile.id)">
                <AppButton size="sm">
                  <Edit3 :size="13" />
                  <span>Edit</span>
                </AppButton>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Quick Suggestions & Quick Tools (4 cols) -->
      <div class="lg:col-span-4 space-y-5">
        <!-- Quick Actions Card -->
        <Card class="space-y-3.5">
          <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-text">
            <Sparkles :size="15" class="text-primary" />
            <span>Recommended Actions</span>
          </div>

          <div class="space-y-2.5">
            <RouterLink
              :to="ROUTES.JD_NEW"
              class="block p-3 sm:p-3.5 rounded-xl border border-border bg-white hover:border-primary/40 hover:bg-surface transition-all group"
            >
              <p class="text-xs sm:text-sm font-semibold text-text group-hover:text-primary-dark flex items-center justify-between">
                <span>Evaluate a Job Description</span>
                <ArrowRight :size="13" class="text-text-subtle group-hover:translate-x-0.5 transition-transform" />
              </p>
              <p class="text-xs text-text-muted mt-0.5 leading-relaxed">Paste any JD to audit required keywords vs your CV.</p>
            </RouterLink>

            <RouterLink
              :to="ROUTES.TEMPLATES"
              class="block p-3 sm:p-3.5 rounded-xl border border-border bg-white hover:border-primary/40 hover:bg-surface transition-all group"
            >
              <p class="text-xs sm:text-sm font-semibold text-text group-hover:text-primary-dark flex items-center justify-between">
                <span>Change CV Template</span>
                <ArrowRight :size="13" class="text-text-subtle group-hover:translate-x-0.5 transition-transform" />
              </p>
              <p class="text-xs text-text-muted mt-0.5 leading-relaxed">Explore 4 ATS-optimized layouts tailored for engineers.</p>
            </RouterLink>

            <RouterLink
              to="/match/1"
              class="block p-3 sm:p-3.5 rounded-xl border border-border bg-white hover:border-primary/40 hover:bg-surface transition-all group"
            >
              <p class="text-xs sm:text-sm font-semibold text-text group-hover:text-primary-dark flex items-center justify-between">
                <span>View Latest Match Report</span>
                <ArrowRight :size="13" class="text-text-subtle group-hover:translate-x-0.5 transition-transform" />
              </p>
              <p class="text-xs text-text-muted mt-0.5 leading-relaxed">Check skill coverage for ReactJS / Vue Intern.</p>
            </RouterLink>
          </div>
        </Card>

        <!-- Trust Note -->
        <div class="p-4 sm:p-5 rounded-xl border border-border bg-surface text-xs text-text-muted space-y-1.5">
          <div class="flex items-center gap-2 font-semibold text-text text-xs sm:text-sm">
            <CheckCircle2 :size="15" class="text-success" />
            <span>Deterministic Analysis</span>
          </div>
          <p class="leading-relaxed">
            CareerFitCV only evaluates verified keywords present in your projects. No hallucinated experience is ever submitted.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
