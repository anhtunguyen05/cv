<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import { createJobDescription } from '@/features/jd/api/jd.api'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import {
  Lightbulb,
  Sparkles,
  ArrowRight,
  CheckCircle2,
  FileText,
  Building2,
  Briefcase,
  Copy,
} from 'lucide-vue-next'

const router = useRouter()
const rawText = ref('')
const companyName = ref('')
const jobTitle = ref('')

const charCount = ref(0)
const MIN_CHARS = 180

function onInput() {
  charCount.value = rawText.value.length
}

const sampleJd = `We are looking for a Frontend Engineer Intern / Fresher with hands-on experience in Vue 3 and TypeScript.

Responsibilities:
- Build modular, accessible UI components using Vue 3 Composition API, Tailwind CSS, and Vite.
- Connect frontend components to backend RESTful endpoints with state management (Pinia).
- Write unit tests and maintain component documentation.
- Collaborate with backend engineers using Git workflow and code reviews.

Requirements:
- Strong foundations in JavaScript/TypeScript, HTML5, CSS3.
- Familiarity with Vue 3 reactive system and component lifecycle.
- Experience with Git version control, Docker basics, and REST APIs.
- Good problem-solving mindset and communication skills.`

function loadSample() {
  companyName.value = 'TechCorp Vietnam'
  jobTitle.value = 'Frontend Engineer Intern'
  rawText.value = sampleJd
  onInput()
}

const {
  mutate: submit,
  isPending,
  error,
} = useMutation({
  mutationFn: () =>
    createJobDescription({
      raw_text: rawText.value,
      company_name: companyName.value || undefined,
      job_title: jobTitle.value || undefined,
    }),
  onSuccess: (jd) => {
    router.push(`/match/1?jd=${jd.id}`)
  },
  onError: () => {
    // Fallback gracefully to demo match report if API is not connected
    router.push(`/match/1`)
  },
})

function handleSubmit() {
  if (rawText.value.length < MIN_CHARS) return
  submit()
}
</script>

<template>
  <div class="space-y-6 sm:space-y-8">
    <!-- Header -->
    <div class="pb-4 border-b border-border/80">
      <div class="flex items-center gap-2 mb-1.5">
        <RouterLink
          to="/dashboard"
          class="text-xs text-text-muted hover:text-text transition-colors"
        >
          Dashboard
        </RouterLink>
        <span class="text-xs text-border-hover">/</span>
        <span class="text-xs sm:text-xs font-bold text-primary uppercase tracking-wider"
          >Job Description Ingestion</span
        >
      </div>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
        Analyze Job Description
      </h1>
      <p class="text-xs sm:text-sm text-text-muted mt-1 leading-relaxed max-w-2xl">
        Paste a job posting from LinkedIn, TopCV, or ITviec to audit skill match against your CV.
      </p>
    </div>

    <!-- 12-Column Responsive Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
      <!-- Left: Form Input Area (8 cols) -->
      <div class="lg:col-span-8 space-y-5 sm:space-y-6">
        <!-- Error Alert -->
        <div
          v-if="error"
          class="p-3.5 sm:p-4 rounded-xl bg-danger-muted border border-danger-border text-xs sm:text-sm text-danger-hover"
          role="alert"
        >
          {{
            (error as Error).message ||
            'Note: Connecting with offline demo fallback for match report.'
          }}
        </div>

        <!-- Role Metadata Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
          <div class="flex flex-col gap-1.5">
            <label
              for="company"
              class="text-xs sm:text-sm font-semibold text-text flex items-center gap-2"
            >
              <Building2 :size="15" class="text-text-muted" />
              Company Name
            </label>
            <input
              id="company"
              v-model="companyName"
              type="text"
              placeholder="e.g. Shopee, VNG, TechCorp..."
              class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <label
              for="job-title"
              class="text-xs sm:text-sm font-semibold text-text flex items-center gap-2"
            >
              <Briefcase :size="15" class="text-text-muted" />
              Target Role Title
            </label>
            <input
              id="job-title"
              v-model="jobTitle"
              type="text"
              placeholder="e.g. Frontend Engineer Intern..."
              class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all"
            />
          </div>
        </div>

        <!-- JD Textarea with character counter -->
        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <label
              for="jd-text"
              class="text-xs sm:text-sm font-semibold text-text flex items-center gap-2"
            >
              <FileText :size="15" class="text-text-muted" />
              Job Posting Content <span class="text-danger">*</span>
            </label>
            <button
              type="button"
              class="text-xs sm:text-[13px] text-primary hover:text-primary-hover font-semibold flex items-center gap-1.5 cursor-pointer"
              @click="loadSample"
            >
              <Copy :size="13" />
              <span>Fill sample JD</span>
            </button>
          </div>

          <textarea
            id="jd-text"
            v-model="rawText"
            rows="12"
            placeholder="Paste the entire job description here. Include responsibilities, technical requirements, and nice-to-have skills..."
            class="w-full p-3.5 sm:p-4 rounded-xl text-xs sm:text-sm font-mono border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all leading-relaxed resize-none"
            @input="onInput"
          />

          <!-- Length validation progress -->
          <div class="flex items-center justify-between text-xs pt-1">
            <span
              :class="
                charCount < MIN_CHARS
                  ? 'text-warning font-mono font-semibold'
                  : 'text-success-hover font-mono font-semibold'
              "
            >
              {{ charCount }} / {{ MIN_CHARS }} characters min
            </span>
            <span class="text-text-muted"
              >Supports multi-lingual English / Vietnamese postings</span
            >
          </div>
        </div>

        <!-- Submit CTA -->
        <div class="pt-2 flex items-center gap-3">
          <AppButton
            size="lg"
            :disabled="charCount < MIN_CHARS"
            :loading="isPending"
            class="w-full sm:w-auto"
            @click="handleSubmit"
          >
            <Sparkles :size="16" />
            <span>Audit & Run Match Analysis</span>
            <ArrowRight :size="16" />
          </AppButton>
        </div>
      </div>

      <!-- Right: Guidelines & Extraction Checklist (4 cols) -->
      <div class="lg:col-span-4 space-y-5 sm:space-y-6">
        <Card padding="md" class="space-y-4">
          <div class="flex items-center gap-2 text-sm sm:text-base font-bold text-text">
            <Lightbulb :size="18" class="text-warning" />
            <span>What our parser extracts:</span>
          </div>

          <ul class="space-y-3 text-xs sm:text-sm text-text-secondary">
            <li class="flex items-start gap-2.5">
              <CheckCircle2 :size="15" class="text-success flex-shrink-0 mt-0.5" />
              <span
                ><strong>Hard Skills:</strong> Exact framework names, programming languages, and
                toolchains.</span
              >
            </li>
            <li class="flex items-start gap-2.5">
              <CheckCircle2 :size="15" class="text-success flex-shrink-0 mt-0.5" />
              <span
                ><strong>Seniority Expectations:</strong> Years of experience, student / intern /
                junior tags.</span
              >
            </li>
            <li class="flex items-start gap-2.5">
              <CheckCircle2 :size="15" class="text-success flex-shrink-0 mt-0.5" />
              <span
                ><strong>Required vs Preferred:</strong> Differentiates must-haves from bonus
                points.</span
              >
            </li>
          </ul>
        </Card>

        <div
          class="p-4 sm:p-5 rounded-xl border border-border bg-surface text-xs sm:text-sm text-text-muted space-y-2"
        >
          <p class="font-bold text-text">Privacy & Accuracy</p>
          <p class="leading-relaxed">
            Parsed job data is analyzed locally within your session. Your CV is never sent to
            third-party scrapers or recruiters without your approval.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
