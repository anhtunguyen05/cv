<script setup lang="ts">
import { ArrowRight, CheckCircle2 } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'

const features = [
  {
    badge: 'Evidence-Based',
    stat: '72%',
    statLabel: 'average match score lift',
    title: 'Deterministic Skill Cross-Referencing',
    desc: 'Unlike generic AI writers that hallucinate fake experience, CareerFitCV extracts actual project evidence from your background and maps it directly to recruiter expectations.',
    highlights: ['Zero fake claims', 'Exact keyword alignment', 'Project-level citations'],
  },
  {
    badge: 'ATS Defense',
    stat: '98%',
    statLabel: 'parser compatibility rate',
    title: 'Engineered for Real Applicant Tracking Systems',
    desc: 'Standard single-column layouts, clean ASCII typography hierarchy, and semantic headings ensure Workday, Greenhouse, and Lever read your resume without distortion.',
    highlights: ['Single-column clean flow', 'No parsing dead-zones', 'Standard section tags'],
  },
  {
    badge: 'Velocity',
    stat: '< 4 min',
    statLabel: 'from JD to polished submission',
    title: 'Fast, Reversible Version Management',
    desc: 'Never overwrite your master profile. Keep individual tailored snapshots for each company application with full version rollback.',
    highlights: ['Immutable version snapshots', 'Side-by-side diffs', 'Target company tagging'],
  },
]
</script>

<template>
  <section id="features" class="bg-dark-bg px-6 lg:px-16 py-20 lg:py-24 border-t border-white/5">
    <div class="max-w-[1440px] mx-auto space-y-20 lg:space-y-24">
      <div
        v-for="(feature, index) in features"
        :key="feature.title"
        :class="[
          'grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center',
          index % 2 ? 'lg:grid-flow-dense' : '',
        ]"
      >
        <div :class="[index % 2 ? 'lg:col-start-2' : '']">
          <div class="glass-panel-dark p-6 sm:p-8 rounded-xl border border-white/10 max-w-md">
            <span
              class="text-xs font-semibold uppercase tracking-wider text-primary-light bg-primary/15 px-2.5 py-1 rounded-full"
              >{{ feature.badge }}</span
            >
            <div
              class="mt-5 text-5xl sm:text-6xl font-extrabold text-white tracking-tight font-mono"
            >
              {{ feature.stat }}
            </div>
            <p class="text-slate-300 text-xs sm:text-sm font-medium mt-1">
              {{ feature.statLabel }}
            </p>
            <div class="mt-5 pt-5 border-t border-white/10 space-y-2">
              <div
                v-for="item in feature.highlights"
                :key="item"
                class="flex items-center gap-2 text-xs text-slate-300"
              >
                <CheckCircle2 :size="13" class="text-success" /><span>{{ item }}</span>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight leading-snug">
            {{ feature.title }}
          </h3>
          <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed">{{ feature.desc }}</p>
          <RouterLink
            :to="ROUTES.REGISTER"
            class="mt-6 inline-flex items-center gap-1.5 text-xs font-semibold text-primary-light hover:text-primary-lighter transition-colors"
          >
            Explore how it works <ArrowRight :size="13" />
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
