<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { Edit3, ZoomIn, ZoomOut, CheckCircle2 } from 'lucide-vue-next'
import type { CvData } from '@/features/cv'

const route = useRoute()
const cvId = route.params.id as string

const zoom = ref(100)

function zoomIn() {
  if (zoom.value < 130) zoom.value += 10
}

function zoomOut() {
  if (zoom.value > 70) zoom.value -= 10
}

// Stub CV data
const cvData: CvData = {
  personal_info: {
    full_name: 'Nguyen Anh Tu',
    email: 'tu@example.com',
    phone: '+84 901 234 567',
    location: 'Ho Chi Minh City, Vietnam',
    github: 'https://github.com/anhtunguyen05',
    linkedin: 'https://linkedin.com/in/anhtunguyen05',
    portfolio: 'https://anhtunguyen.dev',
  },
  summary:
    'Dedicated software engineering student with proven experience building responsive web applications using Vue 3, TypeScript, and REST APIs. Strong focus on clean architecture, component design, and automated testing.',
  skills: {
    frontend: ['Vue 3', 'TypeScript', 'Tailwind CSS v4', 'Vite', 'Pinia', 'HTML5/CSS3'],
    backend: ['Laravel 13', 'PHP', 'MySQL', 'RESTful API Architecture'],
    tools: ['Git', 'Docker', 'Postman', 'Figma', 'Linux'],
  },
  projects: [
    {
      id: '1',
      name: 'CareerFitCV',
      role: 'Fullstack Developer',
      tech_stack: ['Vue 3', 'TypeScript', 'Tailwind CSS', 'Laravel 13'],
      period: 'Sep 2026 – Present',
      bullets: [
        'Architected an ATS CV tailoring system with deterministic evidence verification matching candidate claims against job descriptions.',
        'Engineered responsive single-page application with TanStack Vue Query caching and Pinia client stores.',
        'Designed print-perfect A4 export stylesheet matching international recruitment standards.',
      ],
    },
    {
      id: '2',
      name: 'Edura E-Learning Platform',
      role: 'Frontend Contributor',
      tech_stack: ['Vue 3', 'Vite', 'Pinia', 'Axios'],
      period: 'Jan 2026 – May 2026',
      bullets: [
        'Developed course enrollment workflows and interactive quiz interfaces serving 500+ active students.',
        'Refactored form state into reusable composables, reducing boilerplate by 35%.',
      ],
    },
  ],
  education: [
    {
      school: 'University of Information Technology (UIT)',
      major: 'Software Engineering (Bachelor of Science)',
      period: '2023 – 2027 (Expected)',
      gpa: '3.4 / 4.0',
    },
  ],
  certificates: [
    {
      name: 'Vue.js Certified Developer',
      issuer: 'CertiGlobal',
      date: '2026',
    },
  ],
}
</script>

<template>
  <div class="flex flex-col items-center w-full">
    <!-- Floating Toolbar Controls (in preview canvas) -->
    <div class="no-print mb-6 flex items-center justify-between gap-4 w-full max-w-[794px]">
      <div class="flex items-center gap-3">
        <RouterLink :to="`/cv/${cvId}/edit`">
          <AppButton size="sm" variant="outline">
            <Edit3 :size="14" />
            <span>Edit Sections</span>
          </AppButton>
        </RouterLink>

        <span class="text-sm text-text-muted hidden sm:flex items-center gap-1.5">
          <CheckCircle2 :size="15" class="text-success" />
          <span>ATS Verified Formatting</span>
        </span>
      </div>

      <!-- Zoom controls -->
      <div
        class="flex items-center gap-2 bg-white border border-border px-3 py-1.5 rounded-xl shadow-2xs"
      >
        <button
          type="button"
          class="p-1 text-text-muted hover:text-text rounded-lg cursor-pointer transition-colors"
          aria-label="Zoom out"
          @click="zoomOut"
        >
          <ZoomOut :size="15" />
        </button>
        <span class="text-xs font-mono font-semibold text-text px-1.5">{{ zoom }}%</span>
        <button
          type="button"
          class="p-1 text-text-muted hover:text-text rounded-lg cursor-pointer transition-colors"
          aria-label="Zoom in"
          @click="zoomIn"
        >
          <ZoomIn :size="15" />
        </button>
      </div>
    </div>

    <!-- Scalable Document Surface -->
    <div class="overflow-x-auto w-full flex justify-center pb-12">
      <div
        :style="{
          transform: `scale(${zoom / 100})`,
          transformOrigin: 'top center',
          transition: 'transform 0.15s ease-out',
        }"
      >
        <CvPreviewDocument :data="cvData" />
      </div>
    </div>
  </div>
</template>
