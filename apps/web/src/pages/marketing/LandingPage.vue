<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { ArrowRight, Upload, Search, BarChart2, FileDown } from 'lucide-vue-next'

const steps = [
  { num: '01', icon: Upload, title: 'Build your CV', desc: 'Enter your experience, skills, and projects once.' },
  { num: '02', icon: Search, title: 'Paste a job description', desc: 'Copy any JD and the system extracts what matters.' },
  { num: '03', icon: BarChart2, title: 'Get a match report', desc: 'See matched skills, gaps, and section-level recommendations.' },
  { num: '04', icon: FileDown, title: 'Export a polished CV', desc: 'Pick a template and print or export your tailored CV.' },
]

const features = [
  {
    stat: '72%',
    statLabel: 'average match lift',
    title: 'Precise skill matching',
    desc: 'Every required and nice-to-have skill is cross-referenced with your actual project evidence — not just keyword mentions.',
  },
  {
    stat: 'ATS',
    statLabel: 'keyword coverage',
    title: 'ATS-ready output',
    desc: 'Structured analysis ensures your CV uses the exact language hiring systems scan for.',
  },
  {
    stat: '< 5 min',
    statLabel: 'to a tailored draft',
    title: 'Fast iteration cycle',
    desc: 'From job description to preview in under five minutes. Create separate versions for every application.',
  },
]
</script>

<template>
  <!-- ── Hero ─────────────────────────────────────────────── -->
  <section class="min-h-[100dvh] flex items-center px-6 lg:px-16">
    <div class="w-full max-w-[1440px] mx-auto grid grid-cols-1 lg:grid-cols-[55%_45%] gap-12 py-20">
      <!-- Left: content — left-aligned (no centered hero) -->
      <div class="flex flex-col justify-center stagger-reveal" style="--index: 0;">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/10 bg-white/5 text-slate-300 text-xs font-medium w-fit mb-6">
          <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]" />
          CV + JD matching for students and freshers
        </div>

        <h1 class="text-5xl lg:text-6xl font-bold text-white tracking-tight leading-[1.08]">
          Match your CV<br />
          to every job.<br />
          <span class="text-[#818cf8]">Precisely.</span>
        </h1>

        <p class="mt-5 text-slate-300 text-lg leading-relaxed max-w-[44ch]">
          Analyze skill gaps, generate targeted evidence, and export a polished CV — all in one structured workflow.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink :to="ROUTES.REGISTER">
            <AppButton size="lg">
              Get started free
              <ArrowRight :size="16" :stroke-width="1.5" />
            </AppButton>
          </RouterLink>
          <RouterLink :to="ROUTES.LOGIN">
            <AppButton size="lg" variant="ghost" class="text-slate-200 hover:text-white hover:bg-white/8">
              Sign in
            </AppButton>
          </RouterLink>
        </div>
      </div>

      <!-- Right: CV mockup panel -->
      <div class="hidden lg:flex items-center justify-center stagger-reveal" style="--index: 1;">
        <div class="w-full max-w-md bg-[#20273c] border border-white/8 rounded-xl p-5 shadow-2xl">
          <!-- Mini match report mockup -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-xs text-slate-400">Match Report</p>
              <p class="text-white font-semibold text-sm mt-0.5">ReactJS Intern — TechCorp</p>
            </div>
            <div class="w-12 h-12 rounded-full border-4 border-[#6366f1] flex items-center justify-center">
              <span class="text-white font-bold text-sm">72</span>
            </div>
          </div>

          <div class="space-y-2">
            <div v-for="skill in [
              { name: 'React', status: 'matched' },
              { name: 'TypeScript', status: 'weak' },
              { name: 'REST API', status: 'matched' },
              { name: 'Git', status: 'missing' },
            ]" :key="skill.name" class="flex items-center justify-between py-1.5 border-b border-white/5 last:border-0">
              <span class="text-sm text-slate-200">{{ skill.name }}</span>
              <span :class="[
                'text-xs px-2 py-0.5 rounded-full font-medium',
                skill.status === 'matched' ? 'bg-[#ecfdf5] text-[#059669]' :
                skill.status === 'weak' ? 'bg-[#fffbeb] text-[#b45309]' :
                'bg-[#fef2f2] text-[#dc2626]',
              ]">
                {{ skill.status === 'matched' ? 'Matched' : skill.status === 'weak' ? 'Weak evidence' : 'Missing' }}
              </span>
            </div>
          </div>

          <div class="mt-4 p-3 bg-[#6366f1]/10 rounded-lg border border-[#6366f1]/20">
            <p class="text-xs text-slate-300 leading-relaxed">
              Add TypeScript evidence to your Edura project to improve your score by ~12 points.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── How it works ────────────────────────────────────── -->
  <section class="bg-[#20273c] px-6 lg:px-16 py-20">
    <div class="max-w-[1440px] mx-auto">
      <p class="text-xs font-semibold text-[#818cf8] uppercase tracking-widest mb-3">How it works</p>
      <h2 class="text-3xl font-bold text-white tracking-tight mb-12">Four steps from CV to offer.</h2>

      <!-- Horizontal step flow — not 3 equal cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div
          v-for="(step, i) in steps"
          :key="step.num"
          class="stagger-reveal relative"
          :style="`--index: ${i}`"
        >
          <!-- Step number -->
          <div class="text-5xl font-bold text-white/8 leading-none mb-4">{{ step.num }}</div>
          <div class="w-8 h-8 rounded-lg bg-[#6366f1]/20 flex items-center justify-center mb-3">
            <component :is="step.icon" :size="16" :stroke-width="1.5" class="text-[#818cf8]" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">{{ step.title }}</h3>
          <p class="text-sm text-slate-400 leading-relaxed">{{ step.desc }}</p>

          <!-- Connector line -->
          <div v-if="i < steps.length - 1" class="hidden xl:block absolute top-10 left-full w-full h-px bg-white/8 -translate-x-3" />
        </div>
      </div>
    </div>
  </section>

  <!-- ── Features (zig-zag — not 3 equal cards) ─────────── -->
  <section class="bg-[#1e293b] px-6 lg:px-16 py-20">
    <div class="max-w-[1440px] mx-auto space-y-16">
      <div
        v-for="(feat, i) in features"
        :key="feat.title"
        :class="[
          'grid grid-cols-1 lg:grid-cols-2 gap-12 items-center',
          i % 2 !== 0 ? 'lg:grid-flow-dense' : '',
        ]"
      >
        <!-- Stat side -->
        <div :class="[i % 2 !== 0 ? 'lg:col-start-2' : '']">
          <div class="text-6xl font-bold text-white tracking-tight">{{ feat.stat }}</div>
          <p class="text-[#818cf8] text-sm font-medium mt-1">{{ feat.statLabel }}</p>
        </div>

        <!-- Description side -->
        <div>
          <h3 class="text-xl font-semibold text-white tracking-tight">{{ feat.title }}</h3>
          <p class="mt-2 text-slate-400 leading-relaxed">{{ feat.desc }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── Final CTA ───────────────────────────────────────── -->
  <section class="bg-[#252d45] px-6 py-20">
    <div class="max-w-xl mx-auto text-center">
      <h2 class="text-3xl font-bold text-white tracking-tight">Build a CV that fits.</h2>
      <p class="mt-3 text-slate-400">Start for free. No credit card required.</p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <RouterLink :to="ROUTES.REGISTER">
          <AppButton size="lg">
            Create your account
            <ArrowRight :size="16" :stroke-width="1.5" />
          </AppButton>
        </RouterLink>
      </div>
    </div>
  </section>
</template>
