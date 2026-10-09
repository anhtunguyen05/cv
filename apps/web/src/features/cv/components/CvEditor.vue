<script setup lang="ts">
import { RouterLink } from 'vue-router'
import CvSectionNav from '@/features/cv/components/CvSectionNav.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import FormField from '@/shared/components/molecules/FormField.vue'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import { useCvEditorController } from '@/features/cv/composables/useCvEditorController'
import { ROUTES } from '@/shared/constants/routes'
import type { PersonalInformation } from '@/features/cv/types/cv.types'

const controller = useCvEditorController()
const { editor, profile, version } = controller
</script>

<template>
  <section class="space-y-6" aria-labelledby="cv-editor-title">
    <div
      class="flex flex-col gap-3 border-b border-border pb-4 sm:flex-row sm:items-end sm:justify-between"
    >
      <div>
        <RouterLink :to="ROUTES.DASHBOARD" class="text-xs text-text-muted">Dashboard</RouterLink>
        <h1 id="cv-editor-title" class="mt-1 text-2xl font-bold text-text">CV Profile editor</h1>
        <p class="mt-1 text-sm text-text-muted">
          Changes are saved manually to your private Profile.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <FormField v-slot="{ controlProps }" label="Profile title" html-for="profile-title">
          <input
            v-bind="controlProps"
            v-model="editor.title.value"
            aria-label="Profile title"
            class="h-10 rounded-lg border border-border px-3 text-sm"
            placeholder="Profile title"
          />
        </FormField>
        <Button :loading="editor.saving.value" :disabled="editor.saving.value" @click="editor.save"
          >Save section</Button
        >
      </div>
    </div>

    <p v-if="editor.isDirty.value" class="text-sm text-warning-text" role="status">
      Unsaved Profile changes are kept locally until you save them.
    </p>
    <div
      v-if="editor.errorMessage.value"
      role="alert"
      class="rounded-lg border border-danger-border bg-danger-muted px-4 py-3 text-sm text-danger-hover"
    >
      {{ editor.errorMessage.value }}
      <div v-for="(message, field) in editor.fieldErrors.value" :key="field" class="mt-1">
        {{ field }}: {{ message }}
      </div>
    </div>
    <div
      v-if="profile.loadError.value"
      role="alert"
      class="rounded-lg border border-danger-border bg-danger-muted px-4 py-3 text-sm text-danger-hover"
    >
      Unable to load this Profile.
    </div>

    <div
      class="flex min-h-[540px] flex-col overflow-hidden rounded-xl border border-border bg-white md:flex-row"
    >
      <CvSectionNav
        :active-section="editor.activeSection.value"
        :completed-sections="editor.completedSections.value"
        @select="editor.selectSection"
      />
      <div class="flex-1 space-y-5 p-5 sm:p-8">
        <div
          v-if="editor.activeSection.value === 'personal_information'"
          class="grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-2"
        >
          <h2 class="sm:col-span-2 text-lg font-bold text-text">Personal information</h2>
          <FormField
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
            v-slot="{ controlProps }"
            :label="key.replaceAll('_', ' ')"
            :html-for="`personal-${key}`"
            :error="editor.fieldErrors.value[key]"
            :required="key === 'full_name'"
          >
            <input
              v-bind="controlProps"
              v-model="
                editor.editableDocument.value.personal_information[key as keyof PersonalInformation]
              "
              :type="key === 'email' ? 'email' : key.endsWith('_url') ? 'url' : 'text'"
              class="w-full rounded-lg border border-border px-3 py-2"
            />
          </FormField>
        </div>
        <div v-else class="max-w-4xl space-y-3">
          <h2 class="text-lg font-bold capitalize text-text">
            {{ editor.activeSection.value.replaceAll('_', ' ') }}
          </h2>
          <p class="text-sm text-text-muted">
            Replace this complete section. Existing item IDs are preserved by the server.
          </p>
          <textarea
            v-model="editor.sectionText.value"
            :aria-label="`${editor.activeSection.value} section`"
            class="min-h-[360px] w-full rounded-lg border border-border p-3 font-mono text-sm"
            :placeholder="
              editor.activeSection.value === 'summary'
                ? 'Write a concise professional summary'
                : '[]'
            "
          />
        </div>
      </div>
    </div>

    <div
      v-if="!editor.isNew.value && profile.current.value"
      class="rounded-xl border border-border bg-white p-5"
    >
      <h2 class="font-bold text-text">Create immutable Version</h2>
      <div class="mt-3 flex flex-col gap-2 sm:flex-row">
        <FormField
          v-slot="{ controlProps }"
          label="Version name"
          html-for="version-name"
          class="flex-1"
        >
          <input
            v-bind="controlProps"
            v-model="version.name.value"
            aria-label="Version name"
            class="h-10 w-full rounded-lg border border-border px-3 text-sm"
            placeholder="Version name"
          />
        </FormField>
        <Button
          variant="outline"
          :loading="version.saving.value"
          :disabled="version.saving.value || editor.saving.value"
          @click="version.save"
          >Save Version</Button
        >
      </div>
      <p v-if="version.message.value" class="mt-2 text-sm text-text-muted" role="status">
        {{ version.message.value }}
      </p>
      <ul v-if="version.items.value.length" class="mt-4 space-y-2" aria-label="Saved Versions">
        <li
          v-for="savedVersion in version.items.value"
          :key="savedVersion.id"
          class="rounded-lg border border-border px-3 py-2 text-sm text-text"
        >
          <RouterLink
            :to="ROUTES.CV_VERSION_TEMPLATES(savedVersion.id)"
            class="font-semibold text-primary hover:underline"
          >
            {{ savedVersion.name }}
          </RouterLink>
          <span class="text-text-muted"
            >· source revision {{ savedVersion.source_profile_revision }}</span
          >
        </li>
      </ul>
      <PaginationNav
        class="mt-4"
        :page="version.navigation.page.value"
        :last-page="version.navigation.lastPage.value"
        label="Saved Version pages"
        size="sm"
        @previous="version.navigation.previous"
        @next="version.navigation.next"
      />
    </div>
  </section>
</template>
