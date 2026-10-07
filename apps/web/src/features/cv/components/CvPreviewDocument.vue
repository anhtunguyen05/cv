<script setup lang="ts">
import { computed } from 'vue'
import type { CvPreview } from '../types/cv.types'

const props = defineProps<{ preview: CvPreview }>()

const sectionLabels: Record<string, string> = {
  summary: 'Summary',
  experience: 'Experience',
  projects: 'Projects',
  education: 'Education',
  skills: 'Skills',
  certificates: 'Certificates',
  languages: 'Languages',
  activities: 'Activities',
}

const sectionMap = computed(() => {
  const entries = new Map<string, unknown>()
  for (const section of normalizedSections.value) entries.set(section.key, section.data)
  return entries
})

const normalizedSections = computed(() =>
  Array.isArray(props.preview.sections)
    ? props.preview.sections.filter(
        (section): section is { key: string; data: unknown } =>
          Boolean(section) && typeof section.key === 'string',
      )
    : [],
)

const identity = computed(() => record(sectionMap.value.get('identity')))
const personal = computed(() => record(identity.value.personal_information))
const visibleSections = computed(() => {
  return normalizedSections.value
    .map((section) => section.key)
    .filter(
      (key, index, keys) =>
        key !== 'identity' &&
        Boolean(sectionLabels[key]) &&
        keys.indexOf(key) === index &&
        !isEmpty(sectionMap.value.get(key)),
    )
})

function record(value: unknown): Record<string, unknown> {
  return value && typeof value === 'object' && !Array.isArray(value)
    ? (value as Record<string, unknown>)
    : {}
}

function text(value: unknown, key: string): string {
  const item = record(value)[key]
  return typeof item === 'string' ? item : ''
}

function array(value: unknown): unknown[] {
  return Array.isArray(value) ? value : []
}

function strings(value: unknown): string[] {
  return array(value).filter(
    (item): item is string => typeof item === 'string' && item.trim() !== '',
  )
}

function isEmpty(value: unknown): boolean {
  if (value === null || value === undefined || (typeof value === 'string' && value.trim() === ''))
    return true
  if (Array.isArray(value)) return value.every(isEmpty)
  if (value && typeof value === 'object') return Object.values(value).every(isEmpty)
  return false
}

function itemsFor(section: string): unknown[] {
  return array(sectionMap.value.get(section))
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

function itemMeta(item: unknown, section: string): string {
  const values = ['organization', 'role', 'degree', 'field_of_study', 'issuer', 'proficiency']
    .map((key) => text(item, key))
    .filter(Boolean)
  const start = text(item, 'start_date') || text(item, 'issued_on')
  const end = text(item, 'end_date') || text(item, 'expires_on')
  if (start || end) {
    const displayEnd = end || (section === 'experience' ? 'Present' : '')
    values.push([start, displayEnd].filter(Boolean).map(formatDate).join(' – '))
  }
  return values.join(' · ')
}

function highlights(item: unknown): string[] {
  return strings(record(item).highlights)
}

function technologies(item: unknown): string[] {
  return strings(record(item).technologies)
}

function safeHref(value: string): string | null {
  if (!value) return null
  try {
    const parsed = new URL(value)
    return ['http:', 'https:', 'mailto:'].includes(parsed.protocol.toLowerCase()) ? value : null
  } catch {
    return null
  }
}

function formatDate(value: string): string {
  if (!/^\d{4}-\d{2}$/.test(value)) return value
  const parsed = new Date(`${value}-01T00:00:00Z`)
  if (Number.isNaN(parsed.getTime())) return value
  return new Intl.DateTimeFormat(undefined, {
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(parsed)
}
</script>

<template>
  <article
    class="cv-document paper-shadow min-h-[1123px] w-[794px] bg-white p-12 text-text"
    :data-renderer-version="preview.renderer_version"
    aria-labelledby="cv-document-title"
  >
    <header class="border-b border-border pb-5">
      <h1 id="cv-document-title" class="text-3xl font-bold">{{ text(personal, 'full_name') }}</h1>
      <p v-if="text(identity, 'title')" class="mt-1 text-lg text-text-muted">
        {{ text(identity, 'title') }}
      </p>
      <p v-if="text(personal, 'headline')" class="mt-1 text-lg text-text-muted">
        {{ text(personal, 'headline') }}
      </p>
      <div
        class="mt-2 flex flex-wrap gap-x-2 gap-y-1 text-sm text-text-muted"
        aria-label="Contact information"
      >
        <span v-if="text(personal, 'email')">{{ text(personal, 'email') }}</span>
        <span v-if="text(personal, 'phone')">{{ text(personal, 'phone') }}</span>
        <span v-if="text(personal, 'location')">{{ text(personal, 'location') }}</span>
        <template v-for="key in ['website_url', 'linkedin_url', 'github_url']" :key="key">
          <a
            v-if="safeHref(text(personal, key))"
            :href="safeHref(text(personal, key))!"
            target="_blank"
            rel="noopener noreferrer"
            class="text-primary hover:underline"
          >
            {{ text(personal, key) }}
          </a>
          <span v-else-if="text(personal, key)" class="break-all">{{ text(personal, key) }}</span>
        </template>
      </div>
    </header>

    <section
      v-for="section in visibleSections"
      :key="section"
      class="mt-6"
      :aria-labelledby="`section-${section}`"
    >
      <h2 :id="`section-${section}`" class="text-sm font-bold uppercase tracking-wide">
        {{ sectionLabels[section] }}
      </h2>

      <p v-if="section === 'summary'" class="mt-2 whitespace-pre-wrap text-sm leading-relaxed">
        {{ sectionMap.get(section) }}
      </p>

      <div v-else-if="section === 'skills'" class="mt-2 space-y-1 text-sm">
        <p v-for="group in itemsFor(section)" :key="text(group, 'id') || text(group, 'label')">
          <strong>{{ text(group, 'label') }}:</strong>
          {{
            array(record(group).items)
              .map((item) => text(item, 'name') || String(item))
              .join(', ')
          }}
        </p>
      </div>

      <div v-else class="mt-2 space-y-4">
        <article
          v-for="(item, index) in itemsFor(section)"
          :key="text(item, 'id') || `${itemTitle(item)}-${index}`"
          class="break-inside-avoid break-words border-l-2 border-primary-border pl-3"
        >
          <h3 class="text-sm font-semibold">{{ itemTitle(item) }}</h3>
          <p v-if="itemMeta(item, section)" class="text-xs text-text-muted">
            {{ itemMeta(item, section) }}
          </p>
          <a
            v-if="safeHref(text(item, 'url'))"
            :href="safeHref(text(item, 'url'))!"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-primary hover:underline"
          >
            {{ text(item, 'url') }}
          </a>
          <span v-else-if="text(item, 'url')" class="break-all text-xs text-text-muted">
            {{ text(item, 'url') }}
          </span>
          <a
            v-if="safeHref(text(item, 'credential_url'))"
            :href="safeHref(text(item, 'credential_url'))!"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-primary hover:underline"
          >
            Credential link
          </a>
          <span v-else-if="text(item, 'credential_url')" class="break-all text-xs text-text-muted">
            {{ text(item, 'credential_url') }}
          </span>
          <p v-if="technologies(item).length" class="text-xs text-text-muted">
            Technologies: {{ technologies(item).join(', ') }}
          </p>
          <p
            v-if="text(item, 'description')"
            class="mt-1 whitespace-pre-wrap text-xs leading-relaxed"
          >
            {{ text(item, 'description') }}
          </p>
          <ul v-if="highlights(item).length" class="mt-1 list-disc pl-4 text-xs leading-relaxed">
            <li v-for="(highlight, index) in highlights(item)" :key="`${highlight}-${index}`">
              {{ highlight }}
            </li>
          </ul>
        </article>
      </div>
    </section>
  </article>
</template>
