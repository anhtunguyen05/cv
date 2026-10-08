<script setup lang="ts">
import { BarChart2, FileDown, Search, Upload } from 'lucide-vue-next'

const steps = [
  {
    num: '01',
    icon: Upload,
    title: 'Create or Import CV',
    desc: 'Structured entry for your technical stack, projects, education, and achievements once.',
  },
  {
    num: '02',
    icon: Search,
    title: 'Ingest Job Posting',
    desc: 'Paste any job description. Our parser extracts core requirements and secondary preferences.',
  },
  {
    num: '03',
    icon: BarChart2,
    title: 'Analyze Evidence Gaps',
    desc: 'Receive deterministic match scores, verified keyword citations, and targeted suggestions.',
  },
  {
    num: '04',
    icon: FileDown,
    title: 'Export Tailored PDF',
    desc: 'Select an ATS-optimized template and export a print-ready, high-converting CV.',
  },
]
</script>

<template>
  <section
    id="how-it-works"
    class="bg-dark-raised px-6 lg:px-16 py-20 lg:py-24 border-t border-white/5"
  >
    <div class="max-w-[1440px] mx-auto">
      <div class="max-w-xl mb-12 sm:mb-16">
        <p class="text-xs font-bold text-primary-light uppercase tracking-widest mb-2">Workflow</p>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
          Four steps from job posting to interview-ready CV.
        </h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        <div
          v-for="step in steps"
          :key="step.num"
          class="glass-panel-dark rounded-xl p-6 sm:p-7 border border-white/5 relative group hover:border-primary/40 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-5">
            <div
              class="w-11 h-11 rounded-lg bg-primary/15 border border-primary/30 flex items-center justify-center group-hover:scale-105 transition-transform"
            >
              <component :is="step.icon" :size="20" class="text-primary-light" />
            </div>
            <span
              class="font-mono font-bold text-2xl text-white/25 group-hover:text-primary-light/50 transition-colors"
              >{{ step.num }}</span
            >
          </div>
          <h3 class="text-base sm:text-lg font-bold text-white mb-2">{{ step.title }}</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">{{ step.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
