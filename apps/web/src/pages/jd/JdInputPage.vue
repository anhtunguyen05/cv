<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import { createJobDescription } from '@/features/jd/api/jd.api'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import Card from '@/shared/components/ui/card/Card.vue'
import { Lightbulb } from 'lucide-vue-next'

const router = useRouter()
const rawText = ref('')
const companyName = ref('')
const jobTitle = ref('')

const charCount = ref(0)
const MIN_CHARS = 200

function onInput() {
  charCount.value = rawText.value.length
}

const { mutate: submit, isPending, error } = useMutation({
  mutationFn: () =>
    createJobDescription({
      raw_text: rawText.value,
      company_name: companyName.value || undefined,
      job_title: jobTitle.value || undefined,
    }),
  onSuccess: (jd) => {
    router.push(`/match/new?jd=${jd.id}`)
  },
})

function handleSubmit() {
  if (rawText.value.length < MIN_CHARS) return
  submit()
}
</script>

<template>
  <div class="max-w-2xl space-y-6">
    <SectionHeader
      title="New Job Description"
      description="Paste a job posting to extract required skills and run a match analysis."
      level="h1"
    />

    <!-- Error -->
    <div
      v-if="error"
      class="px-4 py-3 rounded-md bg-[#fef2f2] border border-[#fecaca] text-sm text-[#dc2626]"
      role="alert"
    >
      {{ (error as Error).message || 'Failed to save. Try again.' }}
    </div>

    <!-- Optional metadata -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label for="company" class="text-sm font-medium text-[#0f172a]">Company name</label>
        <input
          id="company"
          v-model="companyName"
          type="text"
          placeholder="TechCorp Inc."
          class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors"
        />
      </div>
      <div class="flex flex-col gap-1.5">
        <label for="job-title" class="text-sm font-medium text-[#0f172a]">Job title</label>
        <input
          id="job-title"
          v-model="jobTitle"
          type="text"
          placeholder="ReactJS Intern"
          class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors"
        />
      </div>
    </div>

    <!-- JD textarea -->
    <div class="flex flex-col gap-1.5">
      <label for="jd-text" class="text-sm font-medium text-[#0f172a]">
        Job description <span class="text-[#ef4444]">*</span>
      </label>
      <textarea
        id="jd-text"
        v-model="rawText"
        rows="14"
        placeholder="Paste the full job description here. Include responsibilities, requirements, and nice-to-have skills..."
        class="px-3 py-2.5 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors resize-none leading-relaxed"
        @input="onInput"
      />
      <div class="flex justify-between text-xs">
        <span :class="charCount < MIN_CHARS ? 'text-[#f59e0b]' : 'text-[#64748b]'">
          {{ charCount }} characters {{ charCount < MIN_CHARS ? `(need ${MIN_CHARS - charCount} more)` : '' }}
        </span>
        <span class="text-[#64748b]">Paste full JD for best results</span>
      </div>
    </div>

    <!-- Tips -->
    <Card>
      <div class="flex gap-3">
        <Lightbulb :size="16" :stroke-width="1.5" class="text-[#f59e0b] flex-shrink-0 mt-0.5" />
        <div class="space-y-1 text-sm text-[#64748b]">
          <p class="font-medium text-[#0f172a]">Tips for better results</p>
          <ul class="list-disc list-inside space-y-0.5 text-xs">
            <li>Include the complete JD with responsibilities, requirements, and about-the-company sections.</li>
            <li>Copy from LinkedIn, VietnamWorks, ITViec, or Topcv directly.</li>
            <li>Longer JDs produce more accurate skill extraction.</li>
          </ul>
        </div>
      </div>
    </Card>

    <!-- CTA -->
    <div class="flex gap-3">
      <AppButton
        :disabled="charCount < MIN_CHARS"
        :loading="isPending"
        @click="handleSubmit"
      >
        Analyze job description
      </AppButton>
    </div>
  </div>
</template>
