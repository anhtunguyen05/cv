<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import CvSectionNav from '@/features/cv/components/CvSectionNav.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import StatusDot from '@/shared/components/atoms/StatusDot.vue'
import { RouterLink } from 'vue-router'
import { Eye, Save } from 'lucide-vue-next'
import type { CvSectionKey, CvData } from '@/features/cv'

const route = useRoute()
const cvId = route.params.id as string
const isNew = cvId === 'new'

const activeSection = ref<CvSectionKey>('personal_info')
const isSaving = ref(false)
const lastSaved = ref<Date | null>(null)

// Stub form data
const cvData = ref<Partial<CvData>>({
  personal_info: {
    full_name: '',
    email: '',
    phone: '',
    location: '',
  },
  summary: '',
  skills: {},
  projects: [],
  education: [],
})

async function save() {
  isSaving.value = true
  // TODO: wire to createCvProfile / updateCvProfile mutation
  await new Promise((r) => setTimeout(r, 800))
  isSaving.value = false
  lastSaved.value = new Date()
}
</script>

<template>
  <div class="flex flex-col h-full min-h-[calc(100dvh-3.5rem)]">
    <!-- Sticky sub-header -->
    <div class="flex items-center justify-between px-1 pb-4 border-b border-[#e2e8f0] mb-0">
      <SectionHeader
        :title="isNew ? 'New CV Profile' : 'Edit CV Profile'"
        level="h1"
      />
      <div class="flex items-center gap-3">
        <StatusDot
          v-if="isSaving || lastSaved"
          :status="isSaving ? 'processing' : 'active'"
          :label="isSaving ? 'Saving...' : `Saved ${lastSaved?.toLocaleTimeString()}`"
        />
        <RouterLink v-if="!isNew" :to="`/cv/${cvId}/preview`">
          <AppButton variant="outline" size="sm">
            <Eye :size="14" :stroke-width="1.5" />
            Preview
          </AppButton>
        </RouterLink>
        <AppButton size="sm" :loading="isSaving" @click="save">
          <Save :size="14" :stroke-width="1.5" />
          Save
        </AppButton>
      </div>
    </div>

    <!-- Editor body: section nav + content panel -->
    <div class="flex flex-1 mt-4 border border-[#e2e8f0] rounded-lg bg-white overflow-hidden">
      <!-- Section navigator -->
      <CvSectionNav
        :active-section="activeSection"
        @select="(s) => (activeSection = s)"
      />

      <!-- Content panel -->
      <div class="flex-1 p-6 overflow-y-auto">
        <!-- Personal Info -->
        <template v-if="activeSection === 'personal_info'">
          <h2 class="text-base font-semibold text-[#0f172a] mb-5">Personal Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
            <div class="flex flex-col gap-1.5">
              <label for="full-name" class="text-sm font-medium text-[#0f172a]">Full name <span class="text-[#ef4444]">*</span></label>
              <input id="full-name" v-model="cvData.personal_info!.full_name" type="text" placeholder="Nguyen Anh Tu" class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="cv-email" class="text-sm font-medium text-[#0f172a]">Email <span class="text-[#ef4444]">*</span></label>
              <input id="cv-email" v-model="cvData.personal_info!.email" type="email" placeholder="you@example.com" class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="phone" class="text-sm font-medium text-[#0f172a]">Phone</label>
              <input id="phone" v-model="cvData.personal_info!.phone" type="tel" placeholder="+84 xxx xxx xxx" class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="location" class="text-sm font-medium text-[#0f172a]">Location</label>
              <input id="location" v-model="cvData.personal_info!.location" type="text" placeholder="Ho Chi Minh City" class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors" />
            </div>
            <div class="flex flex-col gap-1.5 sm:col-span-2">
              <label for="github" class="text-sm font-medium text-[#0f172a]">GitHub URL</label>
              <input id="github" v-model="cvData.personal_info!.github" type="url" placeholder="https://github.com/username" class="h-9 px-3 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors" />
            </div>
          </div>
        </template>

        <!-- Summary -->
        <template v-else-if="activeSection === 'summary'">
          <h2 class="text-base font-semibold text-[#0f172a] mb-5">Professional Summary</h2>
          <div class="max-w-2xl flex flex-col gap-1.5">
            <label for="cv-summary" class="text-sm font-medium text-[#0f172a]">Summary</label>
            <textarea
              id="cv-summary"
              v-model="cvData.summary"
              rows="6"
              placeholder="Motivated software engineering student with experience building..."
              class="px-3 py-2 rounded-md text-sm border border-[#e2e8f0] bg-white focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors resize-none"
            />
            <p class="text-xs text-[#64748b]">Aim for 2–4 concise sentences highlighting your strongest skills and goal.</p>
          </div>
        </template>

        <!-- Other sections — placeholder for future expansion -->
        <template v-else>
          <h2 class="text-base font-semibold text-[#0f172a] mb-5 capitalize">{{ activeSection.replace('_', ' ') }}</h2>
          <p class="text-sm text-[#64748b]">This section is ready to be built out. Content form coming in the next sprint.</p>
        </template>
      </div>
    </div>
  </div>
</template>
