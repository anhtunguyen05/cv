import { computed, ref } from 'vue'
import type { JobDescription, JobDescriptionRevision } from '../types/jd.types'

export function useJdDraft() {
  const saved = ref<JobDescription | null>(null)
  const savedRevision = ref<JobDescriptionRevision | null>(null)
  const rawText = ref('')
  const company = ref('')
  const role = ref('')

  const hasUnsavedChanges = computed(() => {
    const revision = savedRevision.value
    if (!revision) return Boolean(rawText.value || company.value || role.value)
    return (
      rawText.value !== revision.raw_text ||
      (company.value || null) !== revision.company ||
      (role.value || null) !== revision.role
    )
  })

  function load(jobDescription: JobDescription): void {
    saved.value = jobDescription
    savedRevision.value = jobDescription.current_revision
    rawText.value = jobDescription.current_revision?.raw_text ?? ''
    company.value = jobDescription.current_revision?.company ?? ''
    role.value = jobDescription.current_revision?.role ?? ''
  }

  function reset(): void {
    saved.value = null
    savedRevision.value = null
    rawText.value = ''
    company.value = ''
    role.value = ''
  }

  type DraftValues = { rawText: string; company: string; role: string }

  function values(): DraftValues {
    return { rawText: rawText.value, company: company.value, role: role.value }
  }

  function applyMutationResult(
    jobDescription: JobDescription,
    submitted: DraftValues,
    localAtResponse: DraftValues = values(),
  ): void {
    load(jobDescription)
    if (localAtResponse.rawText !== submitted.rawText) rawText.value = localAtResponse.rawText
    if (localAtResponse.company !== submitted.company) company.value = localAtResponse.company
    if (localAtResponse.role !== submitted.role) role.value = localAtResponse.role
  }

  return {
    saved,
    rawText,
    company,
    role,
    hasUnsavedChanges,
    load,
    reset,
    values,
    applyMutationResult,
  }
}
