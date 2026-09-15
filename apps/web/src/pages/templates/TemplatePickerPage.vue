<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import TemplateCard from '@/features/templates/components/TemplateCard.vue'
import type { CvTemplate } from '@/features/templates/types/template.types'
import SectionHeader from '@/shared/components/molecules/SectionHeader.vue'
import SearchInput from '@/shared/components/molecules/SearchInput.vue'
import EmptyState from '@/shared/components/molecules/EmptyState.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { LayoutTemplate } from 'lucide-vue-next'

const router = useRouter()
const search = ref('')
const selected = ref<CvTemplate | null>(null)

// Stub templates for scaffold
const stubTemplates: CvTemplate[] = [
  { id: 1, name: 'Classic', type: 'Professional', preview_image: '', config_json: {}, is_active: true, created_at: '', updated_at: '' },
  { id: 2, name: 'Modern Minimal', type: 'Tech / Startup', preview_image: '', config_json: {}, is_active: true, created_at: '', updated_at: '' },
  { id: 3, name: 'Compact Pro', type: 'Senior / Executive', preview_image: '', config_json: {}, is_active: true, created_at: '', updated_at: '' },
  { id: 4, name: 'Structured', type: 'ATS-optimized', preview_image: '', config_json: {}, is_active: true, created_at: '', updated_at: '' },
]

const filtered = ref(stubTemplates)

function onSearch(q: string) {
  filtered.value = q.trim()
    ? stubTemplates.filter((t) =>
        t.name.toLowerCase().includes(q.toLowerCase()) ||
        t.type.toLowerCase().includes(q.toLowerCase()),
      )
    : stubTemplates
}

function onSelect(template: CvTemplate) {
  selected.value = selected.value?.id === template.id ? null : template
}

function applyTemplate() {
  if (!selected.value) return
  router.push(`/cv/1/edit?template=${selected.value.id}`)
}
</script>

<template>
  <div class="space-y-6">
    <SectionHeader
      title="Templates"
      description="Choose a template for your CV. You can change it anytime."
      level="h1"
    />

    <!-- Filter bar -->
    <div class="flex items-center justify-between gap-4">
      <div class="w-72">
        <SearchInput v-model="search" placeholder="Search templates..." @search="onSearch" />
      </div>

      <AppButton
        v-if="selected"
        size="sm"
        @click="applyTemplate"
      >
        Use {{ selected.name }}
      </AppButton>
    </div>

    <!-- Grid — 2-col on md, 3-col on lg, 4-col on xl -->
    <div
      v-if="filtered.length"
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5"
    >
      <div v-for="template in filtered" :key="template.id">
        <TemplateCard
          :template="template"
          :selected="selected?.id === template.id"
          @select="onSelect"
        />
      </div>
    </div>

    <EmptyState
      v-else
      :icon="LayoutTemplate"
      title="No templates found"
      :description="`No templates match '${search}'.`"
      action-label="Clear search"
      @action="() => { search = ''; onSearch('') }"
    />
  </div>
</template>
