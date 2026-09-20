<script setup lang="ts">
import { ref } from 'vue'
import { Bot, ArrowLeft, Mic, Sparkles, CheckCircle2 } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'

const sampleAnswer = ref(
  'In CareerFitCV, we built our state management and reactive forms around Vue 3 Proxies, avoiding Vue 2 getter/setter overhead. For example, reactive CV data updates dynamically inside the A4 preview canvas with zero re-render stutter.',
)
</script>

<template>
  <div class="space-y-6 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="space-y-2.5 pb-3 border-b border-border/80">
      <RouterLink
        :to="ROUTES.DASHBOARD"
        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-text-muted hover:text-text transition-colors"
      >
        <ArrowLeft :size="15" />
        <span>Back to Dashboard</span>
      </RouterLink>

      <div class="flex items-center justify-between pt-0.5">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-primary uppercase tracking-wider">AI Simulation</span>
            <span class="text-xs text-border-hover">•</span>
            <span class="text-xs text-success-text font-semibold bg-success-muted border border-success-border px-2 py-0.5 rounded-full">Interactive Preview</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
            AI Mock Interview Coach
          </h1>
          <p class="text-xs sm:text-sm text-text-muted mt-1 leading-relaxed max-w-2xl">
            Practice technical interview questions generated directly from your project evidence and target job description.
          </p>
        </div>
      </div>
    </div>

    <!-- Simulated Question Card -->
    <Card class="space-y-5 border border-border">
      <div class="flex items-center gap-3 pb-3.5 border-b border-surface-muted">
        <div class="w-10 h-10 rounded-xl bg-primary-muted border border-primary-border/60 flex items-center justify-center text-primary shadow-2xs flex-shrink-0">
          <Bot :size="20" />
        </div>
        <div>
          <p class="text-sm sm:text-base font-bold text-text">Technical Interviewer AI</p>
          <p class="text-xs text-text-muted">Focus: Vue 3 Reactive Architecture & State Management</p>
        </div>
      </div>

      <div class="p-4 sm:p-5 rounded-xl bg-surface border border-border text-sm sm:text-[15px] font-medium text-text leading-relaxed">
        "Can you explain how Vue 3's Proxy-based reactivity system works compared to Vue 2, and how you structured component state in your CareerFitCV project?"
      </div>

      <!-- Candidate response simulator -->
      <div class="space-y-2">
        <label for="mock-answer" class="text-xs sm:text-sm font-semibold text-text-secondary flex items-center justify-between">
          <span>Your Response (Voice or Text)</span>
          <span class="text-xs text-success-hover font-medium flex items-center gap-1 bg-success-muted border border-success-border px-2 py-0.5 rounded-full">
            <CheckCircle2 :size="13" /> STAR Framework Detected
          </span>
        </label>
        <textarea
          id="mock-answer"
          v-model="sampleAnswer"
          rows="5"
          class="w-full p-3.5 sm:p-4 rounded-xl text-sm border border-border bg-white leading-relaxed focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-text-subtle"
        />
      </div>

      <!-- Feedback chips -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-success-muted border border-success-border flex items-start gap-2.5">
        <Sparkles :size="17" class="text-success-hover flex-shrink-0 mt-0.5" />
        <div class="text-xs sm:text-sm text-success-strong leading-relaxed">
          <strong class="font-semibold text-success-text">Real-time Coach Evaluation:</strong> Excellent technical depth. You grounded your explanation directly in a tangible project outcome with zero reactive stutter.
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-1">
        <AppButton size="md" variant="outline">
          <Mic :size="15" />
          <span>Record Voice Answer</span>
        </AppButton>

        <AppButton size="md">
          <span>Submit & Next Question</span>
        </AppButton>
      </div>
    </Card>
  </div>
</template>
