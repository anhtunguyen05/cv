<script setup lang="ts">
import type { Component } from 'vue'
import type { CvSectionKey } from '../types/cv.types'
import {
  User,
  FileText,
  Code2,
  FolderGit2,
  GraduationCap,
  Award,
  Globe,
  Users,
  CheckCircle2,
} from 'lucide-vue-next'

interface Props {
  activeSection: CvSectionKey
  completedSections?: CvSectionKey[]
}

withDefaults(defineProps<Props>(), {
  completedSections: () => ['personal_info', 'summary', 'skills', 'projects', 'education'],
})

const emit = defineEmits<{ select: [section: CvSectionKey] }>()

const sections: Array<{ key: CvSectionKey; label: string; icon: Component }> = [
  { key: 'personal_info', label: 'Personal Info', icon: User },
  { key: 'summary', label: 'Summary', icon: FileText },
  { key: 'skills', label: 'Skills & Stack', icon: Code2 },
  { key: 'projects', label: 'Projects', icon: FolderGit2 },
  { key: 'education', label: 'Education', icon: GraduationCap },
  { key: 'certificates', label: 'Certificates', icon: Award },
  { key: 'languages', label: 'Languages', icon: Globe },
  { key: 'activities', label: 'Activities', icon: Users },
]
</script>

<template>
  <nav
    class="w-60 flex-shrink-0 border-r border-border/80 py-4 bg-surface/50 flex flex-col justify-between"
  >
    <div>
      <div class="px-4 mb-3 flex items-center justify-between">
        <span class="text-xs font-bold text-text-subtle uppercase tracking-wider">Sections</span>
        <span
          class="text-xs font-mono text-primary bg-primary-muted px-2 py-0.5 rounded-full font-semibold border border-primary-border"
        >
          {{ completedSections.length }}/{{ sections.length }}
        </span>
      </div>

      <ul class="space-y-1 px-2.5">
        <li v-for="section in sections" :key="section.key">
          <button
            type="button"
            :class="[
              'w-full flex items-center justify-between px-3 py-2 text-xs sm:text-[13px] font-medium rounded-lg transition-all duration-150 text-left cursor-pointer group',
              activeSection === section.key
                ? 'bg-white text-primary-dark shadow-card-quiet-strong border border-border font-semibold'
                : 'text-text-muted hover:bg-white/70 hover:text-text',
            ]"
            @click="emit('select', section.key)"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <component
                :is="section.icon"
                :size="15"
                :stroke-width="1.5"
                :class="[
                  'flex-shrink-0 transition-colors',
                  activeSection === section.key
                    ? 'text-primary'
                    : 'text-text-subtle group-hover:text-text-muted',
                ]"
              />
              <span class="truncate">{{ section.label }}</span>
            </div>

            <CheckCircle2
              v-if="completedSections.includes(section.key)"
              :size="14"
              :stroke-width="2"
              class="text-success flex-shrink-0 ml-1"
            />
          </button>
        </li>
      </ul>
    </div>

    <!-- Section completion tip -->
    <div class="px-4 pt-3.5 border-t border-border/80 mt-3">
      <p class="text-xs text-text-muted leading-relaxed">
        Complete at least Personal Info, Skills, and Projects for optimal ATS score.
      </p>
    </div>
  </nav>
</template>
