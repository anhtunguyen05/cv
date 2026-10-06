<script setup lang="ts">
import { computed, ref, toRaw, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import CvSectionNav from '@/features/cv/components/CvSectionNav.vue'
import AppButton from '@/shared/components/atoms/AppButton.vue'
import { ApiRequestError } from '@/shared/api/client'
import {
  createCvProfile,
  updateCvSection,
  updateCvTitle,
  createCvVersion,
} from '@/features/cv/api/cv.api'
import { useCvProfileQuery, useCvVersionsQuery } from '@/features/cv/api/cv.queries'
import { queryClient } from '@/app/providers/vue-query'
import type {
  CvDocument,
  CvProfile,
  CvSectionKey,
  PersonalInformation,
} from '@/features/cv/types/cv.types'

const route = useRoute()
const router = useRouter()
const profileId = computed(() => String(route.params.id ?? 'new'))
const isNew = computed(() => profileId.value === 'new')
const activeSection = ref<CvSectionKey>('personal_information')
const title = ref('')
const sectionText = ref('')
const saving = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string>>({})
const versionName = ref('')
const versionMessage = ref('')
const versionSaving = ref(false)

const blankPersonal = (): PersonalInformation => ({
  full_name: '',
  headline: null,
  email: null,
  phone: null,
  location: null,
  website_url: null,
  linkedin_url: null,
  github_url: null,
})
const blankDocument = (): CvDocument => ({
  personal_information: blankPersonal(),
  summary: null,
  skills: [],
  education: [],
  experience: [],
  projects: [],
  certificates: [],
  languages: [],
  activities: [],
})
const localDocument = ref<CvDocument>(blankDocument())
const currentProfile = ref<CvProfile | null>(null)
const profileQuery = useCvProfileQuery(profileId)
const versionsQuery = useCvVersionsQuery(profileId)
const { isError: profileLoadError } = profileQuery
const versions = computed(() => versionsQuery.data.value ?? [])

const completedSections = computed<CvSectionKey[]>(() => {
  const document = localDocument.value
  const completed: CvSectionKey[] = []
  if (document.personal_information.full_name.trim()) completed.push('personal_information')
  if (document.summary?.trim()) completed.push('summary')
  for (const section of [
    'skills',
    'education',
    'experience',
    'projects',
    'certificates',
    'languages',
    'activities',
  ] as const) {
    if (document[section].length > 0) completed.push(section)
  }
  return completed
})

function loadProfile(profile: CvProfile | undefined) {
  if (!profile) return
  currentProfile.value = profile
  title.value = profile.title
  localDocument.value = structuredClone(toRaw(profile))
  loadSectionText()
}
watch(() => profileQuery.data.value, loadProfile, { immediate: true })
watch(activeSection, loadSectionText)

function loadSectionText() {
  const value = localDocument.value[activeSection.value]
  sectionText.value =
    activeSection.value === 'summary' ? String(value ?? '') : JSON.stringify(value, null, 2)
}

function parseSection(): CvDocument[CvSectionKey] {
  if (activeSection.value === 'summary') return sectionText.value || null
  return JSON.parse(sectionText.value)
}

function hasUnsavedSectionChanges(): boolean {
  if (activeSection.value === 'personal_information') return false
  const current = localDocument.value[activeSection.value]
  const expected =
    activeSection.value === 'summary' ? String(current ?? '') : JSON.stringify(current, null, 2)
  return sectionText.value !== expected
}

function selectSection(section: CvSectionKey): void {
  if (section === activeSection.value) return
  if (
    hasUnsavedSectionChanges() &&
    typeof window !== 'undefined' &&
    !window.confirm('Discard unsaved changes in this section?')
  ) {
    return
  }
  activeSection.value = section
}

function applyError(error: unknown) {
  if (error instanceof ApiRequestError) {
    errorMessage.value = error.message
    fieldErrors.value = Object.fromEntries(
      Object.entries(error.details ?? {}).map(([key, value]) => [
        key,
        typeof value === 'string'
          ? value
          : Array.isArray(value)
            ? typeof value[0] === 'string'
              ? value[0]
              : (value[0]?.message ?? '')
            : 'Invalid value',
      ]),
    )
  } else if (error instanceof SyntaxError) {
    errorMessage.value = 'This section must contain valid JSON.'
  } else {
    errorMessage.value = 'Unable to save this section. Please try again.'
  }
}

async function reconcileConflict(): Promise<void> {
  const draftTitle = title.value
  const draftDocument = structuredClone(localDocument.value)
  const draftSectionText = sectionText.value
  const fresh = await profileQuery.refetch()
  if (!fresh.data) return

  currentProfile.value = fresh.data
  localDocument.value = structuredClone(toRaw(fresh.data))
  title.value = draftTitle
  if (activeSection.value === 'personal_information') {
    localDocument.value.personal_information = draftDocument.personal_information
  } else {
    sectionText.value = draftSectionText
  }
  errorMessage.value =
    'This Profile changed elsewhere. Your draft is kept; review it and save again.'
}

async function save() {
  if (saving.value) return
  saving.value = true
  errorMessage.value = ''
  fieldErrors.value = {}
  try {
    if (isNew.value) {
      const profile = await createCvProfile(title.value, localDocument.value.personal_information)
      await router.replace(`/cv/${profile.id}/edit`)
      loadProfile(profile)
      return
    }
    let profile = currentProfile.value
    if (!profile) return
    if (title.value !== profile.title) {
      profile = await updateCvTitle(profile.id, title.value, profile.revision)
      currentProfile.value = profile
      queryClient.setQueryData(['cv-profiles', profile.id], profile)
    }
    const value = parseSection()
    const updated = await updateCvSection(profile.id, activeSection.value, value, profile.revision)
    currentProfile.value = updated
    localDocument.value = structuredClone(toRaw(updated))
    queryClient.setQueryData(['cv-profiles', updated.id], updated)
    void queryClient.invalidateQueries({ queryKey: ['cv-profiles'] })
    loadSectionText()
  } catch (error) {
    applyError(error)
    if (error instanceof ApiRequestError && error.status === 409) {
      await reconcileConflict()
    }
  } finally {
    saving.value = false
  }
}

async function saveVersion() {
  if (versionSaving.value) return
  const profile = currentProfile.value
  if (!profile || !versionName.value.trim()) return
  versionSaving.value = true
  versionMessage.value = ''
  try {
    await createCvVersion(profile.id, versionName.value.trim(), profile.revision)
    await versionsQuery.refetch()
    void queryClient.invalidateQueries({ queryKey: ['cv-versions'] })
    versionMessage.value = 'Version saved.'
    versionName.value = ''
  } catch (error) {
    versionMessage.value = error instanceof Error ? error.message : 'Unable to save version.'
    if (error instanceof ApiRequestError && error.status === 409) {
      const fresh = await profileQuery.refetch()
      if (fresh.data) {
        currentProfile.value = fresh.data
        queryClient.setQueryData(['cv-profiles', fresh.data.id], fresh.data)
        versionMessage.value =
          'The Profile changed. Review the draft and try Version creation again.'
      }
    }
  } finally {
    versionSaving.value = false
  }
}
</script>

<template>
  <section class="space-y-6" aria-labelledby="cv-editor-title">
    <div
      class="flex flex-col gap-3 border-b border-border pb-4 sm:flex-row sm:items-end sm:justify-between"
    >
      <div>
        <RouterLink to="/dashboard" class="text-xs text-text-muted">Dashboard</RouterLink>
        <h1 id="cv-editor-title" class="mt-1 text-2xl font-bold text-text">CV Profile editor</h1>
        <p class="mt-1 text-sm text-text-muted">
          Changes are saved manually to your private Profile.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <input
          v-model="title"
          aria-label="Profile title"
          class="h-10 rounded-lg border border-border px-3 text-sm"
          placeholder="Profile title"
        />
        <AppButton :loading="saving" :disabled="saving" @click="save">Save section</AppButton>
      </div>
    </div>

    <div
      v-if="errorMessage"
      role="alert"
      class="rounded-lg border border-danger-border bg-danger-muted px-4 py-3 text-sm text-danger-hover"
    >
      {{ errorMessage }}
      <div v-for="(message, field) in fieldErrors" :key="field" class="mt-1">
        {{ field }}: {{ message }}
      </div>
    </div>
    <div
      v-if="profileLoadError"
      role="alert"
      class="rounded-lg border border-danger-border bg-danger-muted px-4 py-3 text-sm text-danger-hover"
    >
      Unable to load this Profile.
    </div>

    <div
      class="flex min-h-[540px] flex-col overflow-hidden rounded-xl border border-border bg-white md:flex-row"
    >
      <CvSectionNav
        :active-section="activeSection"
        :completed-sections="completedSections"
        @select="selectSection"
      />
      <div class="flex-1 space-y-5 p-5 sm:p-8">
        <div
          v-if="activeSection === 'personal_information'"
          class="grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-2"
        >
          <h2 class="sm:col-span-2 text-lg font-bold text-text">Personal information</h2>
          <label
            v-for="key in [
              'full_name',
              'headline',
              'email',
              'phone',
              'location',
              'website_url',
              'linkedin_url',
              'github_url',
            ]"
            :key="key"
            class="space-y-1 text-sm font-medium text-text"
          >
            <span>{{ key.replaceAll('_', ' ') }}<span v-if="key === 'full_name'"> *</span></span>
            <input
              v-model="localDocument.personal_information[key as keyof PersonalInformation]"
              :type="key === 'email' ? 'email' : key.endsWith('_url') ? 'url' : 'text'"
              class="w-full rounded-lg border border-border px-3 py-2"
            />
          </label>
        </div>
        <div v-else class="max-w-4xl space-y-3">
          <h2 class="text-lg font-bold capitalize text-text">
            {{ activeSection.replaceAll('_', ' ') }}
          </h2>
          <p class="text-sm text-text-muted">
            Replace this complete section. Existing item IDs are preserved by the server.
          </p>
          <textarea
            v-model="sectionText"
            :aria-label="`${activeSection} section`"
            class="min-h-[360px] w-full rounded-lg border border-border p-3 font-mono text-sm"
            :placeholder="
              activeSection === 'summary' ? 'Write a concise professional summary' : '[]'
            "
          />
        </div>
      </div>
    </div>

    <div v-if="!isNew && currentProfile" class="rounded-xl border border-border bg-white p-5">
      <h2 class="font-bold text-text">Create immutable Version</h2>
      <div class="mt-3 flex flex-col gap-2 sm:flex-row">
        <input
          v-model="versionName"
          aria-label="Version name"
          class="h-10 flex-1 rounded-lg border border-border px-3 text-sm"
          placeholder="Version name"
        />
        <AppButton
          variant="outline"
          :loading="versionSaving"
          :disabled="versionSaving"
          @click="saveVersion"
          >Save Version</AppButton
        >
      </div>
      <p v-if="versionMessage" class="mt-2 text-sm text-text-muted" role="status">
        {{ versionMessage }}
      </p>
      <ul v-if="versions.length" class="mt-4 space-y-2" aria-label="Saved Versions">
        <li
          v-for="version in versions"
          :key="version.id"
          class="rounded-lg border border-border px-3 py-2 text-sm text-text"
        >
          <RouterLink
            :to="`/cv/${profileId}/version/${version.id}`"
            class="font-semibold text-primary hover:underline"
          >
            {{ version.name }}
          </RouterLink>
          <span class="text-text-muted"
            >· source revision {{ version.source_profile_revision }}</span
          >
        </li>
      </ul>
    </div>
  </section>
</template>
