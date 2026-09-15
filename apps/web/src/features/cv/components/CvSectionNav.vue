<script setup lang="ts">
import type { CvSectionKey } from '../types/cv.types'
import { CheckCircle } from 'lucide-vue-next'

interface Props {
  activeSection: CvSectionKey
  completedSections?: CvSectionKey[]
}

withDefaults(defineProps<Props>(), {
  completedSections: () => [],
})

const emit = defineEmits<{ 'select': [section: CvSectionKey] }>()

const sections: Array<{ key: CvSectionKey; label: string }> = [
  { key: 'personal_info', label: 'Personal Info' },
  { key: 'summary', label: 'Summary' },
  { key: 'skills', label: 'Skills' },
  { key: 'projects', label: 'Projects' },
  { key: 'education', label: 'Education' },
  { key: 'certificates', label: 'Certificates' },
  { key: 'languages', label: 'Languages' },
  { key: 'activities', label: 'Activities' },
]
</script>

<template>
  <nav class="w-48 flex-shrink-0 border-r border-[#e2e8f0] py-4">
    <p class="px-4 text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">Sections</p>
    <ul class="space-y-0.5">
      <li v-for="section in sections" :key="section.key">
        <button
          :class="[
            'w-full flex items-center justify-between px-4 py-2 text-sm transition-colors duration-150 text-left',
            activeSection === section.key
              ? 'bg-[#eef2ff] text-[#4338ca] font-medium border-r-2 border-[#6366f1]'
              : 'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f8fafc]',
          ]"
          @click="emit('select', section.key)"
        >
          {{ section.label }}
          <CheckCircle
            v-if="completedSections.includes(section.key)"
            :size="14"
            :stroke-width="1.5"
            class="text-[#10b981] flex-shrink-0"
          />
        </button>
      </li>
    </ul>
  </nav>
</template>
