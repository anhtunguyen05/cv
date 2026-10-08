<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import Button from '@/shared/components/ui/button/Button.vue'
import {
  ArrowRight,
  CheckCircle2,
  Sparkles,
  ShieldCheck,
  Zap,
} from 'lucide-vue-next'
import LandingWorkflowSection from '@/features/marketing/components/LandingWorkflowSection.vue'
import LandingFeaturesSection from '@/features/marketing/components/LandingFeaturesSection.vue'
</script>

<template>
  <div class="relative overflow-hidden">
    <!-- ── Hero Section ───────────────────────────────────────── -->
    <section class="min-h-[92dvh] flex items-center px-6 lg:px-16 pt-8 pb-16">
      <div
        class="w-full max-w-[1440px] mx-auto grid grid-cols-1 lg:grid-cols-[54%_46%] gap-12 lg:gap-16 items-center"
      >
        <!-- Left: Content (left-aligned) -->
        <div class="flex flex-col justify-center stagger-reveal" style="--index: 0">
          <!-- Kicker Tag -->
          <div
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/10 bg-white/5 text-slate-300 text-xs font-medium w-fit mb-6 shadow-xs"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse" />
            <span>ATS Matching & Evidence Engine for Tech Roles</span>
          </div>

          <!-- Bold Headline -->
          <h1
            class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.08]"
          >
            Match your CV<br />
            to every job.<br />
            <span
              class="text-transparent bg-clip-text bg-gradient-to-r from-primary-light via-primary-lighter to-primary-border"
            >
              With verified evidence.
            </span>
          </h1>

          <!-- Value prop description -->
          <p class="mt-6 text-slate-300 text-base sm:text-lg leading-relaxed max-w-[48ch]">
            Extract required keywords, audit missing skills against real project work, and export an
            ATS-proof CV in minutes. Built specifically for students and engineers.
          </p>

          <!-- CTAs -->
          <div class="mt-8 flex flex-wrap items-center gap-4">
            <RouterLink :to="ROUTES.REGISTER">
              <Button size="lg" class="shadow-brand-action">
                Build your CV free
                <ArrowRight :size="16" :stroke-width="2" />
              </Button>
            </RouterLink>

            <RouterLink :to="ROUTES.LOGIN">
              <Button size="lg" variant="glass"> Sign in to workspace </Button>
            </RouterLink>
          </div>

          <!-- Key trust highlights -->
          <div class="mt-10 flex flex-wrap items-center gap-6 text-sm text-slate-300">
            <div class="flex items-center gap-2">
              <CheckCircle2 :size="16" class="text-success" />
              <span>No credit card required</span>
            </div>
            <div class="flex items-center gap-2">
              <ShieldCheck :size="16" class="text-primary-light" />
              <span>Grounded, truth-first suggestions</span>
            </div>
            <div class="flex items-center gap-2">
              <Zap :size="16" class="text-warning" />
              <span>Export in under 5 minutes</span>
            </div>
          </div>
        </div>

        <!-- Right: Interactive Match Report Mockup -->
        <div class="flex items-center justify-center stagger-reveal" style="--index: 1">
          <div
            class="w-full max-w-lg glass-panel-dark rounded-2xl p-6 sm:p-7 shadow-dark-hero border border-white/10 relative"
          >
            <!-- Header of Mockup Card -->
            <div class="flex items-center justify-between pb-5 border-b border-white/10">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-bold tracking-wider uppercase text-primary-light"
                    >Match Analysis</span
                  >
                  <span
                    class="text-xs bg-success/20 text-success px-2.5 py-0.5 rounded-full font-semibold border border-success/30"
                    >ATS High Fit</span
                  >
                </div>
                <h3 class="text-white font-bold text-lg mt-1">Frontend Engineer (Vue 3 / TS)</h3>
                <p class="text-sm text-slate-400 mt-0.5">TechCorp Global · Intern & Junior</p>
              </div>

              <!-- Score circle badge -->
              <div
                class="w-16 h-16 rounded-full border-4 border-primary bg-primary/10 flex flex-col items-center justify-center flex-shrink-0 shadow-brand-ring"
              >
                <span class="text-white font-extrabold text-xl leading-none">88</span>
                <span class="text-xs text-slate-400 font-mono">/ 100</span>
              </div>
            </div>

            <!-- Skill checklist preview -->
            <div class="py-5 space-y-3">
              <div
                class="flex items-center justify-between p-3.5 rounded-xl bg-white/5 border border-white/5"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-6 h-6 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0"
                  >
                    <CheckCircle2 :size="14" class="text-success" />
                  </div>
                  <span class="text-sm text-slate-200 font-medium">Vue 3 Composition API</span>
                </div>
                <span
                  class="text-xs font-medium text-success bg-success/15 px-2.5 py-1 rounded-full"
                >
                  Evidence: CareerFitCV
                </span>
              </div>

              <div
                class="flex items-center justify-between p-3.5 rounded-xl bg-white/5 border border-white/5"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-6 h-6 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0"
                  >
                    <CheckCircle2 :size="14" class="text-success" />
                  </div>
                  <span class="text-sm text-slate-200 font-medium"
                    >TypeScript & State Management</span
                  >
                </div>
                <span
                  class="text-xs font-medium text-success bg-success/15 px-2.5 py-1 rounded-full"
                >
                  Evidence: Pinia & Types
                </span>
              </div>

              <div
                class="flex items-center justify-between p-3.5 rounded-xl bg-white/5 border border-white/5"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-6 h-6 rounded-full bg-warning/20 flex items-center justify-center flex-shrink-0"
                  >
                    <Sparkles :size="14" class="text-warning" />
                  </div>
                  <span class="text-sm text-slate-200 font-medium">Unit Testing (Vitest)</span>
                </div>
                <span
                  class="text-xs font-medium text-warning bg-warning/15 px-2.5 py-1 rounded-full"
                >
                  Weak Evidence
                </span>
              </div>
            </div>

            <!-- Targeted recommendation box -->
            <div class="p-4 sm:p-5 rounded-xl bg-primary/15 border border-primary/30">
              <div class="flex items-start gap-3">
                <Sparkles :size="18" class="text-primary-light flex-shrink-0 mt-0.5" />
                <p class="text-sm text-indigo-100 leading-relaxed">
                  <strong class="text-white font-semibold">Suggested Fix:</strong> Explicitly
                  mention unit testing in your project bullets to gain an estimated
                  <strong class="text-success">+8 points</strong> on this JD.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <LandingWorkflowSection />
    <LandingFeaturesSection />

    <!-- ── Final High-Impact CTA ──────────────────────────────── -->
    <section class="bg-dark-raised px-6 py-24 border-t border-white/10 relative">
      <div class="max-w-3xl mx-auto text-center space-y-6">
        <div
          class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 text-primary-light text-xs font-semibold"
        >
          <Sparkles :size="13" />
          Ready to level up your job hunt?
        </div>

        <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
          Build a CV that fits.<br />
          Deterministic. Verified. Fast.
        </h2>

        <p class="text-slate-400 text-sm sm:text-base max-w-lg mx-auto">
          Start for free today. Create immutable CV profiles, analyze job postings, and export
          ATS-tested PDFs.
        </p>

        <div class="pt-4 flex flex-wrap justify-center gap-4">
          <RouterLink :to="ROUTES.REGISTER">
            <Button size="lg" class="shadow-brand-hero">
              Create your free account
              <ArrowRight :size="16" :stroke-width="2" />
            </Button>
          </RouterLink>
          <RouterLink :to="ROUTES.LOGIN">
            <Button size="lg" variant="glass"> Sign in to existing account </Button>
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
