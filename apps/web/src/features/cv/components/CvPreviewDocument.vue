<script setup lang="ts">
import type { CvDocument } from '../types/cv.types'

const props = defineProps<{ data: CvDocument & { title?: string } }>()

const repeatableSections = [
  'experience',
  'projects',
  'education',
  'certificates',
  'languages',
  'activities',
] as const

function itemsFor(section: (typeof repeatableSections)[number]): unknown[] {
  const value = props.data[section]
  return Array.isArray(value) ? value : []
}

function text(item: unknown, key: string): string {
  if (!item || typeof item !== 'object') return ''
  const value = (item as Record<string, unknown>)[key]
  return typeof value === 'string' ? value : ''
}

function itemTitle(item: unknown): string {
  return (
    text(item, 'name') ||
    text(item, 'role') ||
    text(item, 'organization') ||
    text(item, 'institution') ||
    text(item, 'language') ||
    'Untitled entry'
  )
}

function itemMeta(item: unknown): string {
  const values = ['organization', 'role', 'degree', 'field_of_study', 'issuer', 'proficiency']
    .map((key) => text(item, key))
    .filter(Boolean)
  const start = text(item, 'start_date') || text(item, 'issued_on')
  const end = text(item, 'end_date') || text(item, 'expires_on')
  if (start || end) values.push([start, end || 'Present'].filter(Boolean).join(' – '))
  return values.join(' · ')
}

function highlights(item: unknown): string[] {
  if (!item || typeof item !== 'object') return []
  const value = (item as Record<string, unknown>).highlights
  return Array.isArray(value)
    ? value.filter((entry): entry is string => typeof entry === 'string')
    : []
}
</script>

<template>
  <article class="min-h-[1123px] w-[794px] bg-white p-12 text-text shadow-lg">
    <header class="border-b border-border pb-5">
      <h1 class="text-3xl font-bold">{{ data.personal_information.full_name }}</h1>
      <p v-if="data.personal_information.headline" class="mt-1 text-lg text-text-muted">
        {{ data.personal_information.headline }}
      </p>
      <p class="mt-2 text-sm text-text-muted">
        {{ data.personal_information.email || '' }}
        <span v-if="data.personal_information.phone"> · {{ data.personal_information.phone }}</span>
        <span v-if="data.personal_information.location">
          · {{ data.personal_information.location }}</span
        >
      </p>
    </header>
    <section v-if="data.summary" class="mt-6">
      <h2 class="text-sm font-bold uppercase">Summary</h2>
      <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed">{{ data.summary }}</p>
    </section>
    <section v-if="data.skills.length" class="mt-6">
      <h2 class="text-sm font-bold uppercase">Skills</h2>
      <p class="mt-2 text-sm">
        {{
          data.skills
            .map((group) => `${group.label}: ${group.items.map((item) => item.name).join(', ')}`)
            .join(' · ')
        }}
      </p>
    </section>
    <section
      v-for="section in repeatableSections"
      :key="section"
      v-show="itemsFor(section).length"
      class="mt-6"
    >
      <h2 class="text-sm font-bold uppercase">{{ section }}</h2>
      <div class="mt-2 space-y-3">
        <article
          v-for="item in itemsFor(section)"
          :key="text(item, 'id') || itemTitle(item)"
          class="border-l-2 border-primary-border pl-3"
        >
          <h3 class="text-sm font-semibold">{{ itemTitle(item) }}</h3>
          <p v-if="itemMeta(item)" class="text-xs text-text-muted">{{ itemMeta(item) }}</p>
          <a
            v-if="text(item, 'url')"
            :href="text(item, 'url')"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-primary hover:underline"
          >
            {{ text(item, 'url') }}
          </a>
          <ul v-if="highlights(item).length" class="mt-1 list-disc pl-4 text-xs leading-relaxed">
            <li v-for="highlight in highlights(item)" :key="highlight">{{ highlight }}</li>
          </ul>
        </article>
      </div>
    </section>
  </article>
</template>
