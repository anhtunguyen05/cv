<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import CvSectionNav from '@/features/cv/components/CvSectionNav.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import StatusDot from '@/shared/components/atoms/StatusDot.vue'
import {
  Eye,
  Save,
  Plus,
  Trash2,
  Sparkles,
  CheckCircle2,
} from 'lucide-vue-next'
import type { CvSectionKey, CvData } from '@/features/cv'

const route = useRoute()
const cvId = route.params.id as string
const isNew = cvId === 'new'

const activeSection = ref<CvSectionKey>('personal_info')
const isSaving = ref(false)
const lastSaved = ref<Date | null>(new Date())

// Sample Initial Form Data
const cvData = ref<CvData>({
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
    frontend: ['Vue 3', 'TypeScript', 'Tailwind CSS', 'Vite', 'HTML/CSS'],
    backend: ['Laravel', 'PHP', 'MySQL', 'REST API'],
    tools: ['Git', 'Docker', 'Postman', 'Figma'],
  },
  projects: [
    {
      id: '1',
      name: 'CareerFitCV',
      role: 'Fullstack Developer',
      tech_stack: ['Vue 3', 'TypeScript', 'Tailwind v4', 'Laravel 13'],
      period: 'Sep 2026 – Present',
      bullets: [
        'Architected a deterministic CV tailoring system cross-referencing candidate project claims against job postings.',
        'Engineered responsive single-page application with TanStack Vue Query and Pinia state management.',
        'Designed ATS-optimized export pipeline rendering print-perfect A4 documents.',
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
        'Refactored legacy form state into reusable composables, reducing boilerplate by 35%.',
      ],
    },
  ],
  education: [
    {
      school: 'University of Information Technology (UIT)',
      major: 'Software Engineering',
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
})

// Quick helper to add project
function addProject() {
  cvData.value.projects.push({
    id: Date.now().toString(),
    name: 'New Project',
    role: 'Developer',
    tech_stack: ['Vue 3', 'TypeScript'],
    period: '2026',
    bullets: ['Implemented key user-facing features and integrated RESTful endpoints.'],
  })
}

function removeProject(index: number) {
  cvData.value.projects.splice(index, 1)
}

// New skill input tag model
const newSkillTag = ref('')
const selectedSkillCategory = ref<'frontend' | 'backend' | 'tools'>('frontend')

function addSkill() {
  const val = newSkillTag.value.trim()
  if (!val) return
  if (!cvData.value.skills[selectedSkillCategory.value]) {
    cvData.value.skills[selectedSkillCategory.value] = []
  }
  cvData.value.skills[selectedSkillCategory.value]?.push(val)
  newSkillTag.value = ''
}

function removeSkill(category: string, skillIndex: number) {
  cvData.value.skills[category]?.splice(skillIndex, 1)
}

async function save() {
  isSaving.value = true
  await new Promise((r) => setTimeout(r, 600))
  isSaving.value = false
  lastSaved.value = new Date()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Top Sub-Header & Sticky Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/80">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <RouterLink to="/dashboard" class="text-xs text-text-muted hover:text-text transition-colors">
            Dashboard
          </RouterLink>
          <span class="text-xs text-border-hover">/</span>
          <span class="text-xs font-bold text-primary uppercase tracking-wider">CV Profile Editor</span>
        </div>

        <div class="flex items-center gap-3">
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
            {{ cvData.personal_info.full_name }}'s CV
          </h1>
          <span class="text-xs font-mono font-semibold px-2.5 py-0.5 rounded-full bg-primary-muted text-primary-dark border border-primary-border">
            {{ isNew ? 'v1.0 (Draft)' : 'v1.2 (Active)' }}
          </span>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-shrink-0">
        <StatusDot
          :status="isSaving ? 'processing' : 'active'"
          :label="isSaving ? 'Saving changes...' : `Saved at ${lastSaved?.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`"
        />

        <RouterLink :to="`/cv/${cvId === 'new' ? '1' : cvId}/preview`">
          <AppButton variant="outline" size="md">
            <Eye :size="16" :stroke-width="1.5" />
            <span>Document Preview</span>
          </AppButton>
        </RouterLink>

        <AppButton size="md" :loading="isSaving" @click="save">
          <Save :size="16" :stroke-width="2" />
          <span>Save Changes</span>
        </AppButton>
      </div>
    </div>

    <!-- Main Workspace: Section Navigator + Section Editor Form -->
    <div class="flex flex-col md:flex-row border border-border rounded-xl bg-white shadow-2xs overflow-hidden min-h-[600px]">
      <!-- Section Navigation Sidebar -->
      <CvSectionNav
        :active-section="activeSection"
        @select="(s) => (activeSection = s)"
      />

      <!-- Editor Content Panel -->
      <div class="flex-1 p-5 sm:p-7 lg:p-8 overflow-y-auto bg-white">
        <!-- ── Personal Info ────────────────────────── -->
        <div v-if="activeSection === 'personal_info'" class="space-y-6 max-w-2xl">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Personal Information</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">Contact details and portfolio links for recruiters to reach you.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <div class="flex flex-col gap-1.5">
              <label for="full-name" class="text-xs sm:text-sm font-semibold text-text">Full Name <span class="text-danger">*</span></label>
              <input id="full-name" v-model="cvData.personal_info.full_name" type="text" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="cv-email" class="text-xs sm:text-sm font-semibold text-text">Email <span class="text-danger">*</span></label>
              <input id="cv-email" v-model="cvData.personal_info.email" type="email" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="phone" class="text-xs sm:text-sm font-semibold text-text">Phone</label>
              <input id="phone" v-model="cvData.personal_info.phone" type="tel" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="location" class="text-xs sm:text-sm font-semibold text-text">Location</label>
              <input id="location" v-model="cvData.personal_info.location" type="text" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="github" class="text-xs sm:text-sm font-semibold text-text">GitHub URL</label>
              <input id="github" v-model="cvData.personal_info.github" type="url" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="linkedin" class="text-xs sm:text-sm font-semibold text-text">LinkedIn URL</label>
              <input id="linkedin" v-model="cvData.personal_info.linkedin" type="url" class="h-9.5 sm:h-10 px-3.5 rounded-lg text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all" />
            </div>
          </div>
        </div>

        <!-- ── Summary ──────────────────────────────── -->
        <div v-else-if="activeSection === 'summary'" class="space-y-6 max-w-2xl">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Professional Summary</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">A 2–3 sentence executive synopsis highlighting your engineering focus.</p>
          </div>

          <div class="space-y-2">
            <textarea
              id="cv-summary"
              v-model="cvData.summary"
              rows="5"
              class="w-full p-3.5 sm:p-4 rounded-xl text-sm border border-border bg-white text-text focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all leading-relaxed"
            />
            <div class="flex items-center justify-between text-xs text-text-muted">
              <span>{{ cvData.summary.length }} characters</span>
              <span class="text-success-hover font-semibold bg-success-muted border border-success-border px-2.5 py-0.5 rounded-full">Optimal: 150–300 chars</span>
            </div>
          </div>

          <div class="p-3.5 sm:p-4 rounded-xl bg-primary-muted border border-primary-border/60 flex items-start gap-2.5">
            <Sparkles :size="17" class="text-primary flex-shrink-0 mt-0.5" />
            <div class="text-xs sm:text-sm text-text-secondary leading-relaxed">
              <strong class="font-semibold text-text">ATS Pro-Tip:</strong> Mention your primary stack (e.g. Vue 3, TypeScript, Laravel) directly in the first sentence to increase keyword match weighting.
            </div>
          </div>
        </div>

        <!-- ── Skills ───────────────────────────────── -->
        <div v-else-if="activeSection === 'skills'" class="space-y-6 max-w-2xl">
          <div>
            <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Technical Skills & Tools</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">Group technical proficiencies for targeted ATS keyword matching.</p>
          </div>

          <!-- Add skill bar -->
          <div class="flex items-center gap-2 p-2 bg-surface border border-border rounded-xl">
            <select
              v-model="selectedSkillCategory"
              class="h-9 px-2.5 rounded-lg text-xs sm:text-sm border border-border bg-white font-medium text-text focus:outline-none"
            >
              <option value="frontend">Frontend</option>
              <option value="backend">Backend</option>
              <option value="tools">Tools / DevOps</option>
            </select>

            <input
              v-model="newSkillTag"
              type="text"
              placeholder="e.g. Pinia, GraphQL, Docker..."
              class="flex-1 h-9 px-3 rounded-lg text-sm border border-border bg-white focus:outline-none focus:border-primary"
              @keyup.enter="addSkill"
            />

            <AppButton size="sm" @click="addSkill">
              <Plus :size="14" />
              <span>Add</span>
            </AppButton>
          </div>

          <!-- Skills categorized list -->
          <div class="space-y-4">
            <div
              v-for="(skills, cat) in cvData.skills"
              :key="cat"
              class="p-4 border border-border rounded-xl bg-white space-y-2.5"
            >
              <span class="text-xs font-bold text-text uppercase tracking-wider">{{ cat }}</span>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="(skill, sIndex) in skills"
                  :key="skill"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs sm:text-[13px] font-medium bg-surface-muted text-text-secondary border border-border hover:border-border-hover group"
                >
                  {{ skill }}
                  <button
                    type="button"
                    class="text-text-subtle hover:text-danger transition-colors cursor-pointer text-sm leading-none"
                    @click="removeSkill(cat as string, sIndex)"
                  >
                    &times;
                  </button>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Projects ─────────────────────────────── -->
        <div v-else-if="activeSection === 'projects'" class="space-y-6 max-w-3xl">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg sm:text-xl font-bold text-text tracking-tight">Projects & Evidence</h2>
              <p class="text-xs sm:text-sm text-text-muted mt-0.5">Project descriptions act as primary truth evidence for match score checks.</p>
            </div>

            <AppButton size="md" variant="outline" @click="addProject">
              <Plus :size="15" />
              <span>Add Project</span>
            </AppButton>
          </div>

          <div class="space-y-4">
            <div
              v-for="(project, pIdx) in cvData.projects"
              :key="project.id"
              class="p-4 sm:p-5 border border-border rounded-xl bg-white space-y-3.5 shadow-2xs"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 flex-1">
                  <div>
                    <label class="text-xs font-bold text-text-muted uppercase tracking-wider">Project Name</label>
                    <input v-model="project.name" type="text" class="w-full h-9 px-3 rounded-lg text-sm border border-border mt-1 text-text focus:outline-none focus:border-primary" />
                  </div>
                  <div>
                    <label class="text-xs font-bold text-text-muted uppercase tracking-wider">Role / Title</label>
                    <input v-model="project.role" type="text" class="w-full h-9 px-3 rounded-lg text-sm border border-border mt-1 text-text focus:outline-none focus:border-primary" />
                  </div>
                </div>

                <button
                  type="button"
                  class="text-text-subtle hover:text-danger p-1.5 rounded-lg transition-colors cursor-pointer"
                  @click="removeProject(pIdx)"
                >
                  <Trash2 :size="16" />
                </button>
              </div>

              <!-- Bullets -->
              <div>
                <label class="text-xs font-bold text-text-muted uppercase tracking-wider">Key Impact Bullets</label>
                <div class="space-y-2 mt-1">
                  <div
                    v-for="(bullet, bIdx) in project.bullets"
                    :key="bIdx"
                    class="flex items-center gap-2"
                  >
                    <span class="text-xs text-text-subtle">•</span>
                    <input
                      v-model="project.bullets[bIdx]"
                      type="text"
                      class="flex-1 h-9 px-3 rounded-lg text-sm border border-border text-text focus:outline-none focus:border-primary"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Other Sections Placeholder with modern fallback ── -->
        <div v-else class="space-y-7 max-w-2xl">
          <div>
            <h2 class="text-xl font-bold text-text tracking-tight capitalize">{{ activeSection.replace('_', ' ') }}</h2>
            <p class="text-sm text-text-muted mt-1">Section configurations and data inputs.</p>
          </div>

          <div class="p-8 rounded-2xl border border-dashed border-border-hover text-center space-y-4">
            <CheckCircle2 :size="28" class="text-success mx-auto" />
            <p class="text-sm text-text-muted leading-relaxed">
              This section is active. Fill in your details below or export directly to document preview.
            </p>
            <RouterLink :to="`/cv/${cvId === 'new' ? '1' : cvId}/preview`">
              <AppButton size="md" variant="outline">
                View in Document Canvas
              </AppButton>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
