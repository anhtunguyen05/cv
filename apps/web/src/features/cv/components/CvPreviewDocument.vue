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
  <!-- Genuine A4 paper document canvas -->
  <article
    class="cv-document bg-white text-text paper-shadow rounded-sm mx-auto transition-all selection:bg-primary/20"
    style="width: 794px; min-height: 1123px; padding: 48px 56px; font-family: 'Outfit', sans-serif"
  >
    <!-- Header: Personal Info -->
    <header class="border-b-2 border-text pb-4 mb-6">
      <h1 class="text-3xl font-extrabold tracking-tight text-text">
        {{ data.personal_info.full_name }}
      </h1>

      <!-- Contact and links row -->
      <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-text-quiet">
        <span v-if="data.personal_info.email" class="font-medium text-text">{{
          data.personal_info.email
        }}</span>
        <span v-if="data.personal_info.phone">• {{ data.personal_info.phone }}</span>
        <span v-if="data.personal_info.location">• {{ data.personal_info.location }}</span>
        <a
          v-if="data.personal_info.github"
          :href="data.personal_info.github"
          target="_blank"
          rel="noopener"
          class="text-primary-dark hover:underline font-medium"
        >
          • GitHub
        </a>
        <a
          v-if="data.personal_info.linkedin"
          :href="data.personal_info.linkedin"
          target="_blank"
          rel="noopener"
          class="text-primary-dark hover:underline font-medium"
        >
          • LinkedIn
        </a>
        <a
          v-if="data.personal_info.portfolio"
          :href="data.personal_info.portfolio"
          target="_blank"
          rel="noopener"
          class="text-primary-dark hover:underline font-medium"
        >
          • Portfolio
        </a>
      </div>
    </header>

    <!-- Professional Summary -->
    <section v-if="data.summary" class="mb-6">
      <h2
        class="text-xs font-bold uppercase tracking-widest text-text border-b border-border pb-1 mb-2"
      >
        Professional Summary
      </h2>
      <p class="text-xs sm:text-[13px] leading-relaxed text-text-secondary">
        {{ data.summary }}
      </p>
    </section>

    <!-- Technical Skills -->
    <section v-if="data.skills && Object.keys(data.skills).length" class="mb-6">
      <h2
        class="text-xs font-bold uppercase tracking-widest text-text border-b border-border pb-1 mb-2"
      >
        Technical Skills
      </h2>
      <div class="space-y-1.5 text-xs sm:text-[13px]">
        <div
          v-for="(skills, category) in data.skills"
          :key="category"
          class="flex items-baseline gap-2"
        >
          <span class="font-semibold text-text capitalize w-24 flex-shrink-0">
            {{ category }}:
          </span>
          <span class="text-text-secondary leading-relaxed">
            {{ skills?.join(', ') }}
          </span>
        </div>
      </div>
    </section>

    <!-- Projects & Evidence -->
    <section v-if="data.projects?.length" class="mb-6">
      <h2
        class="text-xs font-bold uppercase tracking-widest text-text border-b border-border pb-1 mb-3"
      >
        Key Projects
      </h2>
      <div class="space-y-4">
        <div v-for="project in data.projects" :key="project.id" class="space-y-1">
          <div class="flex items-baseline justify-between text-xs sm:text-[13px]">
            <div>
              <span class="font-bold text-text">{{ project.name }}</span>
              <span class="text-text-muted"> — {{ project.role }}</span>
            </div>
            <span v-if="project.period" class="text-xs text-text-muted font-medium flex-shrink-0">
              {{ project.period }}
            </span>
          </div>

          <p v-if="project.tech_stack?.length" class="text-xs font-mono text-primary">
            Tech Stack: {{ project.tech_stack.join(' · ') }}
          </p>

          <ul class="mt-1 space-y-1 text-xs sm:text-[13px] text-text-secondary">
            <li
              v-for="(bullet, i) in project.bullets"
              :key="i"
              class="flex items-start gap-2 leading-relaxed"
            >
              <span class="text-text-subtle flex-shrink-0 select-none">•</span>
              <span>{{ bullet }}</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Education -->
    <section v-if="data.education?.length" class="mb-6">
      <h2
        class="text-xs font-bold uppercase tracking-widest text-text border-b border-border pb-1 mb-2"
      >
        Education
      </h2>
      <div class="space-y-2">
        <div
          v-for="(edu, i) in data.education"
          :key="i"
          class="flex items-baseline justify-between text-xs sm:text-[13px]"
        >
          <div>
            <p class="font-bold text-text">{{ edu.school }}</p>
            <p class="text-xs text-text-quiet">
              {{ edu.major }}<span v-if="edu.gpa" class="font-semibold"> · GPA: {{ edu.gpa }}</span>
            </p>
          </div>
          <span class="text-xs text-text-muted font-medium flex-shrink-0">{{ edu.period }}</span>
        </div>
      </div>
    </section>

    <!-- Certificates -->
    <section v-if="data.certificates?.length" class="mb-6">
      <h2
        class="text-xs font-bold uppercase tracking-widest text-text border-b border-border pb-1 mb-2"
      >
        Certifications & Awards
      </h2>
      <ul class="space-y-1 text-xs sm:text-[13px]">
        <li
          v-for="cert in data.certificates"
          :key="cert.name"
          class="flex justify-between items-baseline"
        >
          <span class="text-text">
            <strong>{{ cert.name }}</strong>
            <span class="text-text-muted">· {{ cert.issuer }}</span>
          </span>
          <span class="text-xs text-text-muted font-medium">{{ cert.date }}</span>
        </li>
      </ul>
    </section>
  </article>
</template>
