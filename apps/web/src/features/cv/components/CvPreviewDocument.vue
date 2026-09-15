<script setup lang="ts">
import type { CvData } from '../types/cv.types'

interface Props {
  data: CvData
  compact?: boolean
}

withDefaults(defineProps<Props>(), {
  compact: false,
})
</script>

<template>
  <!-- White A4 paper surface — real document feel -->
  <div
    class="cv-document bg-white text-[#0f172a] mx-auto"
    style="width: 794px; min-height: 1123px; padding: 48px 56px; font-family: 'Outfit', sans-serif;"
  >
    <!-- Personal Info -->
    <header class="border-b border-[#e2e8f0] pb-5 mb-5">
      <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">
        {{ data.personal_info.full_name }}
      </h1>
      <div class="mt-1.5 flex flex-wrap gap-3 text-sm text-[#64748b]">
        <span v-if="data.personal_info.email">{{ data.personal_info.email }}</span>
        <span v-if="data.personal_info.phone">{{ data.personal_info.phone }}</span>
        <span v-if="data.personal_info.location">{{ data.personal_info.location }}</span>
        <a v-if="data.personal_info.github" :href="data.personal_info.github" class="text-[#6366f1]">GitHub</a>
        <a v-if="data.personal_info.linkedin" :href="data.personal_info.linkedin" class="text-[#6366f1]">LinkedIn</a>
        <a v-if="data.personal_info.portfolio" :href="data.personal_info.portfolio" class="text-[#6366f1]">Portfolio</a>
      </div>
    </header>

    <!-- Summary -->
    <section v-if="data.summary" class="mb-5">
      <h2 class="text-xs font-semibold uppercase tracking-widest text-[#6366f1] mb-2">Summary</h2>
      <p class="text-sm leading-relaxed text-[#334155]">{{ data.summary }}</p>
    </section>

    <!-- Skills -->
    <section v-if="data.skills && Object.keys(data.skills).length" class="mb-5">
      <h2 class="text-xs font-semibold uppercase tracking-widest text-[#6366f1] mb-2">Skills</h2>
      <div class="space-y-1">
        <div v-for="(skills, category) in data.skills" :key="category" class="flex gap-2 text-sm">
          <span class="font-medium text-[#0f172a] capitalize w-20 flex-shrink-0">{{ category }}:</span>
          <span class="text-[#334155]">{{ skills?.join(', ') }}</span>
        </div>
      </div>
    </section>

    <!-- Projects -->
    <section v-if="data.projects?.length" class="mb-5">
      <h2 class="text-xs font-semibold uppercase tracking-widest text-[#6366f1] mb-3">Projects</h2>
      <div class="space-y-4">
        <div v-for="project in data.projects" :key="project.id">
          <div class="flex items-start justify-between">
            <div>
              <span class="font-semibold text-sm text-[#0f172a]">{{ project.name }}</span>
              <span class="text-[#64748b] text-sm"> — {{ project.role }}</span>
            </div>
            <span v-if="project.period" class="text-xs text-[#64748b] flex-shrink-0">{{ project.period }}</span>
          </div>
          <p class="text-xs text-[#94a3b8] mt-0.5">{{ project.tech_stack.join(' · ') }}</p>
          <ul class="mt-1.5 space-y-1">
            <li
              v-for="(bullet, i) in project.bullets"
              :key="i"
              class="text-sm text-[#334155] flex gap-2"
            >
              <span class="text-[#94a3b8] flex-shrink-0">—</span>
              {{ bullet }}
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Education -->
    <section v-if="data.education?.length" class="mb-5">
      <h2 class="text-xs font-semibold uppercase tracking-widest text-[#6366f1] mb-3">Education</h2>
      <div class="space-y-2">
        <div v-for="(edu, i) in data.education" :key="i" class="flex justify-between">
          <div>
            <p class="text-sm font-semibold text-[#0f172a]">{{ edu.school }}</p>
            <p class="text-sm text-[#64748b]">{{ edu.major }}<span v-if="edu.gpa"> · GPA {{ edu.gpa }}</span></p>
          </div>
          <span class="text-sm text-[#64748b] flex-shrink-0">{{ edu.period }}</span>
        </div>
      </div>
    </section>

    <!-- Certificates -->
    <section v-if="data.certificates?.length" class="mb-5">
      <h2 class="text-xs font-semibold uppercase tracking-widest text-[#6366f1] mb-3">Certificates</h2>
      <ul class="space-y-1">
        <li v-for="cert in data.certificates" :key="cert.name" class="flex justify-between text-sm">
          <span class="text-[#0f172a]">{{ cert.name }} <span class="text-[#64748b]">· {{ cert.issuer }}</span></span>
          <span class="text-[#64748b]">{{ cert.date }}</span>
        </li>
      </ul>
    </section>
  </div>
</template>
