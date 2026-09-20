<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import TemplateCard from '@/features/templates/components/TemplateCard.vue'
import type { CvTemplate } from '@/features/templates/types/template.types'
import SearchInput from '@/shared/components/molecules/SearchInput.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { LayoutTemplate, ArrowRight, ShieldCheck } from 'lucide-vue-next'

const router = useRouter()
const search = ref('')
const selected = ref<CvTemplate | null>(null)

// Rich sample templates
const stubTemplates: CvTemplate[] = [
  {
    id: 1,
    name: 'Modern Engineering',
    type: 'Tech & Startups · Single Column',
    preview_image: '',
    config_json: {},
    is_active: true,
    created_at: '',
    updated_at: '',
  },
  {
    id: 2,
    name: 'Classic ATS Minimal',
    type: 'High-Volume ATS · Top Ranked',
    preview_image: '',
    config_json: {},
    is_active: true,
    created_at: '',
    updated_at: '',
  },
  {
    id: 3,
    name: 'Compact Senior Pro',
    type: 'Multi-Project Tech Leads',
    preview_image: '',
    config_json: {},
    is_active: true,
    created_at: '',
    updated_at: '',
  },
  {
    id: 4,
    name: 'Academic & Research',
    type: 'New Graduates & Research',
    preview_image: '',
    config_json: {},
    is_active: true,
    created_at: '',
    updated_at: '',
  },
]

// Pre-select first template by default
selected.value = stubTemplates[0] ?? null

const filtered = ref(stubTemplates)

function filterTemplates() {
  const query = search.value.toLowerCase().trim()
  filtered.value = stubTemplates.filter((t) => {
    const matchesSearch =
      !query ||
      t.name.toLowerCase().includes(query) ||
      t.type.toLowerCase().includes(query)
    return matchesSearch
  })
}

function onSearch(q: string) {
  search.value = q
  filterTemplates()
}

function onSelect(template: CvTemplate) {
  selected.value = selected.value?.id === template.id ? null : template
}

function applyTemplate() {
  if (!selected.value) return
  router.push(`/cv/1/preview?template=${selected.value.id}`)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/80">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <RouterLink to="/dashboard" class="text-xs text-text-muted hover:text-text transition-colors">
            Dashboard
          </RouterLink>
          <span class="text-xs text-border-hover">/</span>
          <span class="text-xs font-bold text-primary uppercase tracking-wider">Templates</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text">
          ATS-Optimized Templates
        </h1>
        <p class="text-xs sm:text-sm text-text-muted mt-1 leading-relaxed max-w-2xl">
          Choose a layout tailored for applicant tracking systems. Formatting stays strictly within standard recruiter rules.
        </p>
      </div>

      <div v-if="selected" class="flex items-center gap-3 flex-shrink-0">
        <AppButton size="md" @click="applyTemplate">
          <span>Apply "{{ selected.name }}"</span>
          <ArrowRight :size="15" />
        </AppButton>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="w-full sm:w-80">
        <SearchInput v-model="search" placeholder="Search templates by style..." @search="onSearch" />
      </div>

      <div class="flex items-center gap-1.5 text-xs font-semibold text-success-text bg-success-muted border border-success-border px-3 py-1.5 rounded-full self-start sm:self-auto shadow-2xs">
        <ShieldCheck :size="15" />
        <span>Certified for Workday, Greenhouse & Lever</span>
      </div>
    </div>

    <!-- Templates Grid -->
    <div
      v-if="filtered.length"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6"
    >
      <div v-for="template in filtered" :key="template.id">
        <TemplateCard
          :template="template"
          :selected="selected?.id === template.id"
          @select="onSelect"
        />
      </div>
    </div>

    <!-- Empty search state -->
    <EmptyState
      v-else
      :icon="LayoutTemplate"
      title="No templates found"
      :description="`No templates match your search '${search}'.`"
      action-label="Reset search"
      @action="() => { search = ''; filterTemplates(); }"
    />
  </div>
</template>
