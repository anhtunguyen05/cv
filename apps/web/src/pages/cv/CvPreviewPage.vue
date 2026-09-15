<script setup lang="ts">
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import type { CvData } from '@/features/cv'

// Stub CV data — will be replaced by useCvVersionQuery
const stubData: CvData = {
  personal_info: {
    full_name: 'Nguyen Anh Tu',
    email: 'tu@example.com',
    phone: '+84 9xx xxx xxx',
    location: 'Ho Chi Minh City',
    github: 'https://github.com/anhtunguyen05',
    linkedin: 'https://linkedin.com/in/anhtunguyen05',
  },
  summary:
    'Software engineering student passionate about building clean, user-focused web applications. Experienced with Vue 3, Laravel, and REST APIs through personal and team projects.',
  skills: {
    frontend: ['Vue 3', 'TypeScript', 'Tailwind CSS', 'HTML/CSS'],
    backend: ['Laravel', 'PHP', 'MySQL', 'REST API'],
    tools: ['Git', 'Docker', 'Vite', 'Figma'],
  },
  projects: [
    {
      id: '1',
      name: 'CareerFitCV',
      role: 'Fullstack Developer',
      tech_stack: ['Vue 3', 'TypeScript', 'Laravel', 'MySQL'],
      bullets: [
        'Built a CV tailoring platform matching candidates to job descriptions with automated skill gap analysis.',
        'Implemented JWT auth and RESTful API with Laravel 13.',
        'Designed responsive UI with Tailwind v4 and Vite.',
      ],
      period: 'Sep 2026 – Present',
    },
  ],
  education: [
    {
      school: 'University of Information Technology',
      major: 'Software Engineering',
      period: '2023 – 2027',
      gpa: '3.4',
    },
  ],
}
</script>

<template>
  <div class="flex flex-col items-center">
    <!-- Controls slot (PreviewLayout renders these) -->
    <!-- PreviewLayout's #controls slot is used from parent — here we pass extra controls -->

    <!-- Document canvas -->
    <div class="overflow-x-auto py-4 w-full flex justify-center">
      <CvPreviewDocument :data="stubData" />
    </div>
  </div>
</template>
