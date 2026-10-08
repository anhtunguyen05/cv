<script setup lang="ts">
import { ArrowRight, Briefcase, Plus, Sparkles } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import Button from '@/shared/components/ui/button/Button.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import DashboardStats from './DashboardStats.vue'
import CvProfileList from './CvProfileList.vue'
import JdList from './JdList.vue'
import { useDashboardController } from '../composables/useDashboardController'

const controller = useDashboardController()
</script>

<template>
  <div class="space-y-6">
    <div
      class="flex flex-col gap-4 border-b border-border/80 pb-4 md:flex-row md:items-center md:justify-between"
    >
      <div>
        <div class="mb-1 flex items-center gap-2">
          <span class="text-xs font-bold uppercase tracking-wider text-primary">Overview</span>
          <span class="h-1 w-1 rounded-full bg-border-hover" />
          <span class="text-xs font-medium text-text-muted">Candidate Workspace</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-text sm:text-3xl">Candidate Workspace</h1>
        <p class="mt-1 max-w-2xl text-xs leading-relaxed text-text-muted sm:text-sm">
          Manage trusted CV profiles, evaluate job descriptions, and review source-pinned reports.
        </p>
      </div>
      <div class="flex shrink-0 items-center gap-2.5">
        <RouterLink :to="ROUTES.JD_NEW">
          <Button variant="outline" size="md"><Briefcase :size="15" /> Analyze Job</Button>
        </RouterLink>
        <RouterLink :to="ROUTES.CV_EDIT('new')">
          <Button size="md"><Plus :size="15" /> New CV Profile</Button>
        </RouterLink>
      </div>
    </div>

    <DashboardStats :controller="controller" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
      <div class="space-y-8 lg:col-span-8">
        <CvProfileList :controller="controller" />
        <JdList :controller="controller" />
      </div>

      <aside class="space-y-5 lg:col-span-4">
        <Card class="space-y-3.5">
          <div class="flex items-center gap-2 text-sm font-bold text-text">
            <Sparkles :size="15" class="text-primary" />
            <span>Recommended Actions</span>
          </div>
          <RouterLink
            :to="ROUTES.JD_NEW"
            class="group block rounded-xl border border-border bg-white p-3.5 transition-all hover:border-primary/40 hover:bg-surface"
          >
            <p
              class="flex items-center justify-between text-sm font-semibold text-text group-hover:text-primary-dark"
            >
              Evaluate a Job Description <ArrowRight :size="13" />
            </p>
            <p class="mt-0.5 text-xs leading-relaxed text-text-muted">
              Audit required keywords against your CV.
            </p>
          </RouterLink>
          <RouterLink
            :to="ROUTES.TEMPLATES"
            class="group block rounded-xl border border-border bg-white p-3.5 transition-all hover:border-primary/40 hover:bg-surface"
          >
            <p
              class="flex items-center justify-between text-sm font-semibold text-text group-hover:text-primary-dark"
            >
              Change CV Template <ArrowRight :size="13" />
            </p>
            <p class="mt-0.5 text-xs leading-relaxed text-text-muted">
              Preview a saved immutable Version.
            </p>
          </RouterLink>
        </Card>
        <Card class="space-y-3">
          <h2 class="text-sm font-bold text-text">Workspace guidance</h2>
          <p class="text-xs leading-relaxed text-text-muted">
            Keep the master Profile factual, then use evidence-backed Match Reports to decide which
            changes to make.
          </p>
        </Card>
      </aside>
    </div>
  </div>
</template>
