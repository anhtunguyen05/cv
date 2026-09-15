<script setup lang="ts">
import { useRoute, RouterLink } from 'vue-router'
import MatchScoreRing from '@/features/match/components/MatchScoreRing.vue'
import SkillGapList from '@/features/match/components/SkillGapList.vue'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import Progress from '@/shared/components/ui/progress/Progress.vue'
import AppBadge from '@/shared/components/atoms/AppBadge.vue'
import { ArrowLeft, ArrowRight } from 'lucide-vue-next'

const route = useRoute()
const matchId = Number(route.params.matchId)

// Stub data for when API is not available
const stubReport = {
  id: matchId || 1,
  cv_version_id: 1,
  job_description_id: 1,
  score: 72,
  matched_skills_json: [
    { skill: 'Vue 3', status: 'matched' as const, evidence: 'CareerFitCV project' },
    { skill: 'TypeScript', status: 'matched' as const, evidence: 'Multiple projects' },
    { skill: 'REST API', status: 'matched' as const, evidence: 'Laravel backend' },
  ],
  missing_skills_json: [
    { skill: 'Redux / Pinia', status: 'missing' as const },
    { skill: 'Unit Testing', status: 'missing' as const },
  ],
  weak_sections_json: [
    { skill: 'Docker', status: 'weak' as const, evidence: 'Mentioned once in tools' },
  ],
  recommendations_json: [
    {
      section: 'Projects',
      recommendation: 'Add a TypeScript-specific callout in the Edura project — mention type safety improvements.',
      priority: 'high' as const,
    },
    {
      section: 'Skills',
      recommendation: 'List Pinia explicitly (not just state management) — it matches the JD keyword exactly.',
      priority: 'medium' as const,
    },
  ],
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
}

const priorityColors = {
  high: { variant: 'danger' as const, label: 'High priority' },
  medium: { variant: 'warning' as const, label: 'Medium' },
  low: { variant: 'muted' as const, label: 'Low' },
}
</script>

<template>
  <div class="space-y-6">
    <!-- Nav breadcrumb -->
    <RouterLink
      to="/dashboard"
      class="inline-flex items-center gap-1.5 text-sm text-[#64748b] hover:text-[#0f172a] transition-colors"
    >
      <ArrowLeft :size="14" :stroke-width="1.5" />
      Dashboard
    </RouterLink>

    <SectionHeader
      title="Match Report"
      description="Skill gap analysis between your CV and the job description."
      level="h1"
    />

    <!-- Main grid: score ring + skill list + recommendations -->
    <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6">
      <!-- Left: Score panel -->
      <div class="space-y-4">
        <Card>
          <MatchScoreRing :score="stubReport.score" />
        </Card>

        <!-- Section scores -->
        <Card>
          <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-4">Section scores</p>
          <div class="space-y-4">
            <Progress label="Technical Skills" :value="78" color="primary" />
            <Progress label="Projects" :value="65" color="primary" />
            <Progress label="Summary" :value="50" color="warning" />
            <Progress label="Education" :value="90" color="success" />
          </div>
        </Card>

        <!-- Actions -->
        <RouterLink :to="`/cv/1/edit`">
          <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-md border border-[#e2e8f0] bg-white text-sm font-medium text-[#0f172a] hover:bg-[#f8fafc] hover:border-[#c7d2fe] transition-colors">
            Improve CV
            <ArrowRight :size="14" :stroke-width="1.5" />
          </button>
        </RouterLink>
      </div>

      <!-- Right: Skill gap + recommendations -->
      <div class="space-y-6">
        <!-- Skill gap list -->
        <Card>
          <p class="text-sm font-semibold text-[#0f172a] mb-5">Skill coverage</p>
          <SkillGapList
            :matched="stubReport.matched_skills_json"
            :missing="stubReport.missing_skills_json"
            :weak="stubReport.weak_sections_json"
          />
        </Card>

        <!-- Recommendations -->
        <Card>
          <p class="text-sm font-semibold text-[#0f172a] mb-5">Recommendations</p>
          <div class="space-y-4">
            <div
              v-for="(rec, i) in stubReport.recommendations_json"
              :key="i"
              class="flex gap-3 pb-4 border-b border-[#f1f5f9] last:border-0 last:pb-0"
            >
              <div class="w-1 rounded-full flex-shrink-0 mt-1" :class="rec.priority === 'high' ? 'bg-[#ef4444]' : rec.priority === 'medium' ? 'bg-[#f59e0b]' : 'bg-[#e2e8f0]'" style="min-height: 40px;" />
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-semibold text-[#0f172a]">{{ rec.section }}</span>
                  <AppBadge :variant="priorityColors[rec.priority].variant" :label="priorityColors[rec.priority].label" />
                </div>
                <p class="text-sm text-[#334155] leading-relaxed">{{ rec.recommendation }}</p>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </div>
</template>
