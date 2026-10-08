import { computed, ref } from 'vue'
import type { CvDocument, CvProfile, CvSectionKey, PersonalInformation } from '../types/cv.types'
import {
  cvDocumentSchema,
  cvSectionSchemas,
  personalInformationSchema,
} from '../schemas/cv.schemas'

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

export const blankCvDocument = (): CvDocument => ({
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

function cloneDocument(document: CvDocument): CvDocument {
  return JSON.parse(JSON.stringify(document)) as CvDocument
}

function documentFromProfile(profile: CvProfile): CvDocument {
  return cvDocumentSchema.parse(profile)
}

const sectionKeys: CvSectionKey[] = [
  'personal_information',
  'summary',
  'skills',
  'education',
  'experience',
  'projects',
  'certificates',
  'languages',
  'activities',
]

const equal = (left: unknown, right: unknown): boolean =>
  JSON.stringify(left) === JSON.stringify(right)

function isSectionComplete(key: CvSectionKey, value: CvDocument[CvSectionKey]): boolean {
  if (key === 'personal_information') {
    return Boolean((value as PersonalInformation).full_name.trim())
  }
  if (key === 'summary') {
    return Boolean((value as string | null)?.trim())
  }

  return Array.isArray(value) && value.length > 0
}

export function useCvEditorDraft() {
  const activeSection = ref<CvSectionKey>('personal_information')
  const title = ref('')
  const savedTitle = ref('')
  const sectionText = ref('')
  const savedDocument = ref<CvDocument>(blankCvDocument())
  const editableDocument = ref<CvDocument>(blankCvDocument())

  function loadSectionText(): void {
    const value = editableDocument.value[activeSection.value]
    sectionText.value =
      activeSection.value === 'summary' ? String(value ?? '') : JSON.stringify(value, null, 2)
  }

  function loadProfile(profile: CvProfile): void {
    const document = documentFromProfile(profile)
    savedTitle.value = profile.title
    title.value = profile.title
    savedDocument.value = cloneDocument(document)
    editableDocument.value = cloneDocument(document)
    loadSectionText()
  }

  function reset(): void {
    savedTitle.value = ''
    title.value = ''
    savedDocument.value = blankCvDocument()
    editableDocument.value = blankCvDocument()
    sectionText.value = ''
  }

  function sectionValue(): CvDocument[CvSectionKey] {
    const key = activeSection.value
    if (key === 'personal_information') {
      return personalInformationSchema.parse(editableDocument.value.personal_information)
    }

    if (key === 'summary') {
      return cvSectionSchemas.summary.parse(sectionText.value || null)
    }

    const parsedJson: unknown = JSON.parse(sectionText.value)
    return cvSectionSchemas[key].parse(parsedJson)
  }

  function commitSection(value: CvDocument[CvSectionKey]): void {
    const key = activeSection.value
    if (key === 'personal_information') {
      editableDocument.value.personal_information = value as PersonalInformation
    } else {
      editableDocument.value[key] = value as never
    }
  }

  function isSectionDirty(): boolean {
    const key = activeSection.value
    if (key === 'personal_information') {
      return (
        JSON.stringify(editableDocument.value.personal_information) !==
        JSON.stringify(savedDocument.value.personal_information)
      )
    }

    const savedValue = savedDocument.value[key]
    const expected =
      key === 'summary' ? String(savedValue ?? '') : JSON.stringify(savedValue, null, 2)
    return sectionText.value !== expected
  }

  const isDirty = computed(() => title.value !== savedTitle.value || isSectionDirty())

  function discardSection(): void {
    const key = activeSection.value
    editableDocument.value[key] = JSON.parse(JSON.stringify(savedDocument.value[key])) as never
    loadSectionText()
  }

  function setActiveSection(section: CvSectionKey): void {
    activeSection.value = section
    loadSectionText()
  }

  function applySavedProfile(profile: CvProfile): void {
    loadProfile(profile)
  }

  function applyServerProfilePreservingDraft(profile: CvProfile): void {
    const localSectionText = sectionText.value
    const localActiveSection = activeSection.value
    const activeWasDirty = isSectionDirty()
    const serverDocument = documentFromProfile(profile)
    const previousSaved = savedDocument.value
    const localDocument = captureDocument()
    if (title.value === savedTitle.value) title.value = profile.title
    savedTitle.value = profile.title
    savedDocument.value = cloneDocument(serverDocument)
    editableDocument.value = cloneDocument(serverDocument)
    const clonedLocalDocument = cloneDocument(localDocument)
    for (const key of sectionKeys) {
      if (!equal(localDocument[key], previousSaved[key])) {
        editableDocument.value[key] = clonedLocalDocument[key] as never
      }
    }
    loadSectionText()
    if (activeWasDirty && activeSection.value === localActiveSection)
      sectionText.value = localSectionText
  }

  function applySavedTitle(profile: CvProfile): void {
    savedTitle.value = profile.title
  }

  function applyMutationResult(
    profile: CvProfile,
    submitted: {
      title: string
      section: CvSectionKey
      sectionText: string
      personalInformation: PersonalInformation
      sectionValue: CvDocument[CvSectionKey]
    },
    localAtResponse = {
      title: title.value,
      document: captureDocument(),
      activeSection: activeSection.value,
      sectionText: sectionText.value,
    },
  ): void {
    const serverDocument = documentFromProfile(profile)
    const previousSaved = savedDocument.value

    savedTitle.value = profile.title
    title.value = localAtResponse.title !== submitted.title ? localAtResponse.title : profile.title
    savedDocument.value = cloneDocument(serverDocument)
    editableDocument.value = cloneDocument(serverDocument)
    const clonedLocalDocument = cloneDocument(localAtResponse.document)
    for (const key of sectionKeys) {
      const comparison = key === submitted.section ? submitted.sectionValue : previousSaved[key]
      if (!equal(localAtResponse.document[key], comparison)) {
        editableDocument.value[key] = clonedLocalDocument[key] as never
      }
    }
    loadSectionText()
    if (
      localAtResponse.activeSection === activeSection.value &&
      (localAtResponse.activeSection !== submitted.section ||
        localAtResponse.sectionText !== submitted.sectionText)
    ) {
      sectionText.value = localAtResponse.sectionText
    }
  }

  function captureDocument(): CvDocument {
    const document = cloneDocument(editableDocument.value)
    try {
      document[activeSection.value] = sectionValue() as never
    } catch {
      // Keep the last valid structured value while malformed text remains visible.
    }
    return document
  }

  const completedSections = computed<CvSectionKey[]>(() => {
    const completed: CvSectionKey[] = []
    for (const key of sectionKeys) {
      let value = editableDocument.value[key]
      if (key === activeSection.value) {
        try {
          value = sectionValue()
        } catch {
          // Keep the last valid committed value while malformed text remains editable.
        }
      }
      if (isSectionComplete(key, value)) completed.push(key)
    }
    return completed
  })

  return {
    activeSection,
    title,
    sectionText,
    editableDocument,
    isDirty,
    isSectionDirty,
    completedSections,
    loadProfile,
    reset,
    sectionValue,
    commitSection,
    discardSection,
    setActiveSection,
    applySavedProfile,
    applyServerProfilePreservingDraft,
    applySavedTitle,
    applyMutationResult,
    captureDocument,
  }
}
