import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ZodError } from 'zod'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'
import type { CvProfile, CvSectionKey, PersonalInformation } from '../types/cv.types'
import { useCvEditorDraft } from './useCvEditorDraft'
import { useCvEditorOperations } from './useCvEditorOperations'

export function useCvEditorController() {
  const route = useRoute()
  const router = useRouter()
  const profileId = computed(() => String(route.params.id ?? 'new'))
  const isNew = computed(() => profileId.value === 'new')
  const draft = useCvEditorDraft()
  const { activeSection, title, sectionText, editableDocument, isDirty, completedSections } = draft
  const currentProfile = ref<CvProfile | null>(null)
  const saving = ref(false)
  const errorMessage = ref('')
  const fieldErrors = ref<Record<string, string>>({})
  const versionName = ref('')
  const versionMessage = ref('')
  const versionSaving = ref(false)
  const versionPage = ref(1)
  const profileRouteGeneration = ref(0)
  const createdRouteId = ref('')
  const operations = useCvEditorOperations(profileId, versionPage)
  const { profileQuery, versionsQuery } = operations
  const versionNavigation = usePageNavigation({
    page: versionPage,
    lastPage: () => versionsQuery.data.value?.lastPage,
    isFetching: versionsQuery.isFetching,
  })
  const versions = computed(() => versionsQuery.data.value?.items ?? [])

  function loadProfile(profile: CvProfile | undefined): void {
    if (!profile || profile.id !== profileId.value) return
    currentProfile.value = profile
    draft.applySavedProfile(profile)
  }

  watch(
    [profileId, () => profileQuery.data.value],
    ([id, profile]) => {
      if (id === 'new') {
        currentProfile.value = null
        draft.reset()
        return
      }
      if (!profile || profile.id !== id) return
      if (
        draft.isDirty.value &&
        currentProfile.value?.id === id &&
        currentProfile.value.revision !== profile.revision
      ) {
        currentProfile.value = profile
        draft.applyServerProfilePreservingDraft(profile)
        errorMessage.value =
          'This Profile changed on the server. Your local draft is kept; review it and save again.'
        return
      }
      if (!draft.isDirty.value || currentProfile.value?.id !== id) loadProfile(profile)
    },
    { immediate: true },
  )

  watch(profileId, () => {
    profileRouteGeneration.value += 1
    if (createdRouteId.value === profileId.value) {
      createdRouteId.value = ''
      return
    }
    currentProfile.value = null
    versionPage.value = 1
    draft.reset()
    saving.value = false
    versionSaving.value = false
    errorMessage.value = ''
    fieldErrors.value = {}
    versionMessage.value = ''
    versionName.value = ''
  })

  const requestIsCurrent = (id: string, generation: number) =>
    profileId.value === id && profileRouteGeneration.value === generation

  function applyError(error: unknown): void {
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
    } else if (error instanceof SyntaxError || error instanceof ZodError) {
      errorMessage.value = 'This section must contain valid data for its schema.'
    } else errorMessage.value = 'Unable to save this section. Please try again.'
  }

  async function reconcileConflict(id: string, generation: number): Promise<void> {
    if (!requestIsCurrent(id, generation)) return
    const fresh = await profileQuery.refetch()
    if (!requestIsCurrent(id, generation) || !fresh.data || fresh.data.id !== id) return
    currentProfile.value = fresh.data
    draft.applyServerProfilePreservingDraft(fresh.data)
    operations.cacheProfile(fresh.data)
    errorMessage.value = 'This Profile changed elsewhere. Your draft is kept; save it again.'
  }

  function selectSection(section: CvSectionKey): void {
    if (section === activeSection.value) return
    if (saving.value) {
      try {
        draft.commitSection(draft.sectionValue())
        draft.setActiveSection(section)
      } catch (error) {
        applyError(error)
      }
      return
    }
    if (
      draft.isSectionDirty() &&
      typeof window !== 'undefined' &&
      !window.confirm('Discard unsaved changes in this section?')
    )
      return
    if (draft.isSectionDirty()) draft.discardSection()
    draft.setActiveSection(section)
  }

  async function save(): Promise<void> {
    if (saving.value) return
    saving.value = true
    errorMessage.value = ''
    fieldErrors.value = {}
    let titleWasSaved = false
    const requestedId = profileId.value
    const generation = profileRouteGeneration.value
    let submittedSectionValue: ReturnType<typeof draft.sectionValue>
    try {
      submittedSectionValue = draft.sectionValue()
    } catch (error) {
      applyError(error)
      saving.value = false
      return
    }
    const submission = {
      title: title.value,
      section: activeSection.value,
      sectionText: sectionText.value,
      personalInformation: { ...editableDocument.value.personal_information },
      sectionValue: submittedSectionValue,
    }
    try {
      if (isNew.value) {
        const personalInformation: PersonalInformation = editableDocument.value.personal_information
        let profile = await operations.createProfile(submission.title, personalInformation)
        if (!requestIsCurrent(requestedId, generation)) return
        operations.cacheProfile(profile)
        if (submission.section !== 'personal_information') {
          profile = await operations.updateSection(
            profile.id,
            submission.section,
            submission.sectionValue,
            profile.revision,
          )
          if (!requestIsCurrent(requestedId, generation)) return
        }
        const localAtResponse = {
          title: title.value,
          document: draft.captureDocument(),
          activeSection: activeSection.value,
          sectionText: sectionText.value,
        }
        currentProfile.value = profile
        draft.applyMutationResult(profile, submission, localAtResponse)
        operations.cacheProfile(profile)
        await operations.invalidateProfiles()
        createdRouteId.value = profile.id
        saving.value = false
        await router.replace(ROUTES.CV_EDIT(profile.id))
        return
      }
      let profile = currentProfile.value
      if (!profile) return
      const titleDirty = title.value !== profile.title
      const sectionDirty = draft.isSectionDirty()
      const sectionValue = sectionDirty ? draft.sectionValue() : undefined
      if (!titleDirty && !sectionDirty) {
        errorMessage.value = 'There are no unsaved changes to save.'
        return
      }
      if (titleDirty) {
        profile = await operations.updateTitle(profile.id, submission.title, profile.revision)
        if (!requestIsCurrent(requestedId, generation)) return
        titleWasSaved = true
        currentProfile.value = profile
        draft.applySavedTitle(profile)
        operations.cacheProfile(profile)
      }
      if (sectionDirty && sectionValue !== undefined) {
        profile = await operations.updateSection(
          profile.id,
          submission.section,
          sectionValue,
          profile.revision,
        )
        if (!requestIsCurrent(requestedId, generation)) return
        const localAtResponse = {
          title: title.value,
          document: draft.captureDocument(),
          activeSection: activeSection.value,
          sectionText: sectionText.value,
        }
        currentProfile.value = profile
        draft.applyMutationResult(profile, submission, localAtResponse)
        operations.cacheProfile(profile)
      }
      if (titleWasSaved || sectionDirty) await operations.invalidateProfiles()
    } catch (error) {
      if (!requestIsCurrent(requestedId, generation)) return
      if (titleWasSaved) {
        errorMessage.value =
          'The Profile title was saved, but the section was not. Review and retry.'
        await operations.invalidateProfiles()
      } else applyError(error)
      if (error instanceof ApiRequestError && error.status === 409) {
        await reconcileConflict(requestedId, generation)
        if (titleWasSaved)
          errorMessage.value =
            'The Profile title was saved, but the section was not. Your draft is kept; review and retry.'
      }
    } finally {
      if (requestIsCurrent(requestedId, generation)) saving.value = false
    }
  }

  async function saveVersion(): Promise<void> {
    if (versionSaving.value) return
    const profile = currentProfile.value
    if (!profile || !versionName.value.trim()) return
    if (isDirty.value) {
      versionMessage.value = 'Save all Profile changes before creating an immutable Version.'
      return
    }
    versionSaving.value = true
    versionMessage.value = ''
    const generation = profileRouteGeneration.value
    try {
      await operations.createVersion(profile.id, versionName.value.trim(), profile.revision)
      if (!requestIsCurrent(profile.id, generation)) return
      await operations.invalidateVersions()
      if (!requestIsCurrent(profile.id, generation)) return
      versionMessage.value = 'Version saved.'
      versionName.value = ''
    } catch (error) {
      if (!requestIsCurrent(profile.id, generation)) return
      versionMessage.value = error instanceof Error ? error.message : 'Unable to save version.'
      if (error instanceof ApiRequestError && error.status === 409) {
        const fresh = await profileQuery.refetch()
        if (requestIsCurrent(profile.id, generation) && fresh.data?.id === profile.id) {
          currentProfile.value = fresh.data
          draft.applyServerProfilePreservingDraft(fresh.data)
          operations.cacheProfile(fresh.data)
          versionMessage.value = 'The Profile changed. Review it and try Version creation again.'
        }
      }
    } finally {
      if (requestIsCurrent(profile.id, generation)) versionSaving.value = false
    }
  }

  const editor = {
    isNew,
    activeSection,
    title,
    sectionText,
    editableDocument,
    isDirty,
    completedSections,
    saving,
    errorMessage,
    fieldErrors,
    selectSection,
    save,
  }
  const profile = {
    current: currentProfile,
    loadError: profileQuery.isError,
  }
  const version = {
    name: versionName,
    message: versionMessage,
    saving: versionSaving,
    items: versions,
    navigation: versionNavigation,
    save: saveVersion,
  }

  return { editor, profile, version }
}

export type CvEditorController = ReturnType<typeof useCvEditorController>
export type CvEditorContract = CvEditorController['editor']
export type CvProfileContract = CvEditorController['profile']
export type CvVersionContract = CvEditorController['version']
