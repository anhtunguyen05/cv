<script setup lang="ts">
import { useRoute, RouterLink } from 'vue-router'
import MatchScoreRing from '@/features/match/components/MatchScoreRing.vue'
import SkillGapList from '@/features/match/components/SkillGapList.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import Progress from '@/shared/components/ui/progress/Progress.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import {
  ArrowLeft,
  ArrowRight,
  Sparkles,
  CheckCircle2,
} from 'lucide-vue-next'

const route = useRoute()
const matchId = Number(route.params.matchId)

// Sample report
const stubReport = {
  id: matchId || 1,
  cv_version_id: 1,
  job_description_id: 1,
  company_name: 'TechCorp Vietnam',
  job_title: 'Frontend Engineer (Vue 3 / TypeScript)',
  score: 82,
  matched_skills_json: [
    { skill: 'Vue 3 Composition API', status: 'matched' as const, evidence: 'CareerFitCV & Edura projects' },
    { skill: 'TypeScript & Typings', status: 'matched' as const, evidence: 'All recent repository work' },
    { skill: 'RESTful API & HTTP', status: 'matched' as const, evidence: 'Laravel backend integration' },
    { skill: 'Tailwind CSS', status: 'matched' as const, evidence: 'Modern styling setup' },
  ],
  missing_skills_json: [
    { skill: 'Pinia State Management', status: 'missing' as const },
    { skill: 'Unit Testing (Vitest/Jest)', status: 'missing' as const },
  ],
  weak_sections_json: [
    { skill: 'Docker Containerization', status: 'weak' as const, evidence: 'Only listed in tools list, missing project bullets' },
  ],
  recommendations_json: [
    {
      section: 'Projects',
      recommendation: 'Add a bullet in CareerFitCV highlighting Pinia state architecture — it directly addresses the JD keyword requirements.',
      priority: 'high' as const,
      impact: '+6 pts',
    },
    {
      section: 'Skills',
      recommendation: 'Include Vitest or unit test experience in technical skills section to satisfy the testing requirement.',
      priority: 'high' as const,
      impact: '+5 pts',
    },
    {
      section: 'Summary',
      recommendation: 'Mention "TypeScript" and "RESTful API" within your professional summary for higher front-loaded keyword frequency.',
      priority: 'medium' as const,
      impact: '+3 pts',
    },
  ],
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
}

const priorityColors = {
  high: { variant: 'danger' as const, label: 'High Priority' },
  medium: { variant: 'warning' as const, label: 'Medium' },
  low: { variant: 'muted' as const, label: 'Optional' },
}
</script>

<template>
  <div class="space-y-6">
    <!-- Top breadcrumb & Header -->
    <div class="space-y-2.5 pb-4 border-b border-border/80">
      <RouterLink
        to="/dashboard"
        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-text-muted hover:text-text transition-colors"
      >
        <ArrowLeft :size="15" />
        <span>Back to Dashboard</span>
      </RouterLink>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-0.5">
        <div>
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-bold text-primary uppercase tracking-wider">Evaluation Result</span>
            <span class="text-xs text-border-hover">•</span>
            <span class="text-xs font-semibold text-text-quiet bg-surface-muted px-2.5 py-0.5 rounded-full border border-border">{{ stubReport.company_name }}</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
            {{ stubReport.job_title }}
          </h1>
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
          <RouterLink to="/cv/1/edit">
            <AppButton size="md">
              <span>Apply Fixes in Editor</span>
              <ArrowRight :size="15" />
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Main Grid: Left Score Panel (4 cols) + Right Skill Details (8 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
      <!-- Left Column: Overall score + Section Breakdown -->
      <div class="lg:col-span-4 space-y-5">
        <!-- Overall Score Ring Card -->
        <Card class="bg-gradient-to-b from-white to-surface space-y-4">
          <MatchScoreRing :score="stubReport.score" />

          <div class="p-3 rounded-xl bg-success-muted border border-success-border flex items-center justify-center gap-2 text-xs sm:text-sm text-success-text">
            <CheckCircle2 :size="16" class="flex-shrink-0 text-success" />
            <span class="font-medium">Passed ATS qualification threshold</span>
          </div>
        </Card>

        <!-- Section breakdown scores -->
        <Card class="space-y-4">
          <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">
            Section Breakdown
          </span>

          <div class="space-y-3.5">
            <Progress label="Hard Skills" :value="84" color="primary" />
            <Progress label="Project Evidence" :value="78" color="primary" />
            <Progress label="Education & Degree" :value="95" color="success" />
            <Progress label="Summary Keyword Alignment" :value="60" color="warning" />
          </div>
        </Card>

        <!-- Quick improvement CTA: Dark Card with High Contrast -->
        <Card variant="dark" class="space-y-3.5">
          <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-primary-light">
            <Sparkles :size="15" />
            <span>Predicted Score Boost</span>
          </div>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
            Applying the top 2 suggestions will raise your compatibility score from
            <strong class="text-white font-mono font-bold">{{ stubReport.score }}%</strong>
            to
            <strong class="text-success-light font-mono font-bold">93%</strong>.
          </p>
          <RouterLink to="/cv/1/edit" class="block pt-1">
            <AppButton size="sm" variant="glass" class="w-full">
              Open CV Editor
            </AppButton>
          </RouterLink>
        </Card>
      </div>

      <!-- Right Column: Skills Coverage + Actionable AI Suggestions -->
      <div class="lg:col-span-8 space-y-6">
        <!-- Skill Coverage -->
        <Card class="space-y-4">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Keyword Coverage & Evidence</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5 leading-relaxed">Audited cross-reference between your project bullets and the target job description.</p>
          </div>

          <SkillGapList
            :matched="stubReport.matched_skills_json"
            :missing="stubReport.missing_skills_json"
            :weak="stubReport.weak_sections_json"
          />
        </Card>

        <!-- Actionable Recommendations -->
        <Card class="space-y-4">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Targeted Recommendations</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5 leading-relaxed">Evidence-backed suggestions to close missing keyword gaps.</p>
          </div>

          <div class="space-y-3">
            <div
              v-for="(rec, i) in stubReport.recommendations_json"
              :key="i"
              class="p-4 sm:p-5 rounded-xl border border-border bg-white hover:border-border-hover hover:shadow-2xs transition-all space-y-2"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="text-xs sm:text-sm font-bold text-text uppercase tracking-wider">{{ rec.section }} Section</span>
                  <AppBadge :variant="priorityColors[rec.priority].variant" :label="priorityColors[rec.priority].label" size="sm" />
                </div>
                <span class="text-xs font-mono font-bold text-success-hover bg-success-muted px-2.5 py-0.5 rounded-full border border-success-border">
                  {{ rec.impact }}
                </span>
              </div>

              <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                {{ rec.recommendation }}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </div>
</template>
