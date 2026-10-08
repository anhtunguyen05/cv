import { nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import SearchInput from '@/shared/components/molecules/SearchInput.vue'
import CvSectionNav from '@/features/cv/components/CvSectionNav.vue'
import { useCvEditorDraft } from '@/features/cv/composables/useCvEditorDraft'
import { useJdDraft } from '@/features/jd/composables/useJdDraft'
import { countCodePoints, limitCodePoints } from '@/features/jd/utils/textLimits'
import { cvProfileSchema } from '@/features/cv/schemas/cv.schemas'
import { cvTemplateSchema } from '@/features/templates/schemas/template.schemas'
import { jdAnalysisSchema } from '@/features/jd/schemas/jd.schemas'

describe('web review regressions', () => {
  afterEach(() => {
    vi.useRealTimers()
  })

  it('keeps SearchInput controlled when its parent resets the value', async () => {
    vi.useFakeTimers()
    const wrapper = mount(SearchInput, { props: { modelValue: 'old search' } })

    await wrapper.setProps({ modelValue: '' })
    await nextTick()
    await vi.advanceTimersByTimeAsync(350)

    expect(wrapper.get('input').element.value).toBe('')
    expect(wrapper.emitted('update:modelValue')).toBeUndefined()
    expect(wrapper.emitted('search')).toBeUndefined()
  })

  it('cancels a pending SearchInput emission when its parent supplies a new value', async () => {
    vi.useFakeTimers()
    const wrapper = mount(SearchInput, { props: { modelValue: '' } })

    await wrapper.get('input').setValue('stale user search')
    await wrapper.setProps({ modelValue: 'parent search' })
    await vi.advanceTimersByTimeAsync(350)

    expect(wrapper.get('input').element.value).toBe('parent search')
    expect(wrapper.emitted('search')).toBeUndefined()
  })

  it('renders every CV section, including experience', () => {
    const wrapper = mount(CvSectionNav, {
      props: { activeSection: 'personal_information' },
    })

    expect(wrapper.text()).toContain('Experience')
    expect(wrapper.text()).toContain('0/9')
  })

  it('detects personal-information changes and preserves them across a server refresh', () => {
    const draft = useCvEditorDraft()
    const profile = {
      id: 'profile-1',
      title: 'Master CV',
      revision: 1,
      created_at: '2026-10-08T00:00:00Z',
      updated_at: '2026-10-08T00:00:00Z',
      personal_information: {
        full_name: 'Candidate',
        headline: null,
        email: null,
        phone: null,
        location: null,
        website_url: null,
        linkedin_url: null,
        github_url: null,
      },
      summary: null,
      skills: [],
      education: [],
      experience: [],
      projects: [],
      certificates: [],
      languages: [],
      activities: [],
    }

    draft.loadProfile(profile)
    draft.editableDocument.value.personal_information.full_name = 'Updated Candidate'
    expect(draft.isDirty.value).toBe(true)

    draft.applyServerProfilePreservingDraft({
      ...profile,
      revision: 2,
      title: 'Remote CV',
      summary: 'Remote clean summary',
    })

    expect(draft.title.value).toBe('Remote CV')
    expect(draft.editableDocument.value.personal_information.full_name).toBe('Updated Candidate')
    expect(draft.editableDocument.value.summary).toBe('Remote clean summary')
    expect(draft.isDirty.value).toBe(true)
  })

  it('advances the CV saved snapshot without replacing a newer post-submit edit', () => {
    const draft = useCvEditorDraft()
    const profile = {
      id: 'profile-1',
      title: 'Master CV',
      revision: 1,
      created_at: '2026-10-08T00:00:00Z',
      updated_at: '2026-10-08T00:00:00Z',
      personal_information: {
        full_name: 'Candidate',
        headline: null,
        email: null,
        phone: null,
        location: null,
        website_url: null,
        linkedin_url: null,
        github_url: null,
      },
      summary: null,
      skills: [],
      education: [],
      experience: [],
      projects: [],
      certificates: [],
      languages: [],
      activities: [],
    }

    draft.loadProfile(profile)
    draft.setActiveSection('summary')
    draft.sectionText.value = 'Submitted summary'
    const submission = {
      title: profile.title,
      section: 'summary' as const,
      sectionText: 'Submitted summary',
      personalInformation: structuredClone(profile.personal_information),
    }
    draft.sectionText.value = 'Newer local summary'
    draft.applyMutationResult(
      { ...profile, revision: 2, summary: 'Submitted summary' },
      submission,
    )

    expect(draft.sectionText.value).toBe('Newer local summary')
    expect(draft.isDirty.value).toBe(true)
  })

  it('reports completion from the active unsaved section draft', () => {
    const draft = useCvEditorDraft()
    draft.setActiveSection('summary')
    draft.sectionText.value = 'A locally drafted summary'

    expect(draft.completedSections.value).toContain('summary')
  })

  it('keeps the last committed completion when the active draft is malformed', () => {
    const draft = useCvEditorDraft()
    draft.editableDocument.value.projects = [
      {
        id: 'project-1',
        name: 'Committed project',
        role: null,
        url: null,
        start_date: null,
        end_date: null,
        technologies: [],
        highlights: [],
      },
    ]
    draft.setActiveSection('projects')
    draft.sectionText.value = '{ malformed'

    expect(draft.completedSections.value).toContain('projects')
    expect(() => draft.completedSections.value).not.toThrow()
  })

  it('limits Unicode input by code point without splitting surrogate pairs', () => {
    expect(countCodePoints('A😀é')).toBe(4)
    expect(limitCodePoints('😀ABC', 1)).toBe('😀')
    expect(limitCodePoints('plain text', 50)).toBe('plain text')
  })

  it('resets JD draft state when the resource changes', () => {
    const draft = useJdDraft()
    const jobDescription = {
      id: 'jd-1',
      company: 'Company',
      role: 'Engineer',
      current_revision: {
        id: 'revision-1',
        job_description_id: 'jd-1',
        revision_number: 1,
        raw_text: 'Old source',
        company: 'Company',
        role: 'Engineer',
        created_at: '2026-10-08T00:00:00Z',
      },
      deleted_at: null,
      created_at: '2026-10-08T00:00:00Z',
      updated_at: '2026-10-08T00:00:00Z',
    }

    draft.load(jobDescription)
    draft.rawText.value = 'Local draft'
    draft.reset()

    expect(draft.saved.value).toBeNull()
    expect(draft.rawText.value).toBe('')
    expect(draft.hasUnsavedChanges.value).toBe(false)
  })

  it('advances the JD saved snapshot without replacing a newer post-submit edit', () => {
    const draft = useJdDraft()
    const jobDescription = {
      id: 'jd-1',
      company: 'Company',
      role: 'Engineer',
      current_revision: {
        id: 'revision-1',
        job_description_id: 'jd-1',
        revision_number: 1,
        raw_text: 'Old source',
        company: 'Company',
        role: 'Engineer',
        created_at: '2026-10-08T00:00:00Z',
      },
      deleted_at: null,
      created_at: '2026-10-08T00:00:00Z',
      updated_at: '2026-10-08T00:00:00Z',
    }

    draft.load(jobDescription)
    const submitted = { rawText: 'Submitted source', company: 'Company', role: 'Engineer' }
    draft.rawText.value = 'Newer local source'
    draft.applyMutationResult(
      {
        ...jobDescription,
        current_revision: {
          ...jobDescription.current_revision,
          id: 'revision-2',
          revision_number: 2,
          raw_text: submitted.rawText,
        },
      },
      submitted,
    )

    expect(draft.rawText.value).toBe('Newer local source')
    expect(draft.hasUnsavedChanges.value).toBe(true)
  })

  it('rejects malformed CV section input before it can be committed', () => {
    const draft = useCvEditorDraft()
    draft.setActiveSection('projects')
    draft.sectionText.value = '{ malformed'

    expect(() => draft.sectionValue()).toThrow()
    expect(draft.editableDocument.value.projects).toEqual([])
  })

  it('rejects an invalid CV API response shape at the boundary', () => {
    expect(() => cvProfileSchema.parse({ id: 'profile-1', revision: 'not-a-number' })).toThrow()
  })

  it('normalizes the backend empty-list template metadata representation', () => {
    const template = cvTemplateSchema.parse({
      id: 'template-1',
      version: '1.0.0',
      name: 'Clean Modern',
      description: null,
      status: 'active',
      supported_sections: [],
      preview_metadata: [],
    })

    expect(template.preview_metadata).toEqual({})
  })

  it('accepts strings and labelled objects in JD signal lists', () => {
    const analysis = jdAnalysisSchema.parse({
      id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
      job_description_revision_id: '01ARZ3NDEKTSV4RRFFQ69G5FAW',
      analysis_schema_version: '1.0.0',
      analysis_rule_version: '1.0.0',
      status: 'succeeded',
      created_at: '2026-10-08T00:00:00Z',
      signals: {
        role: { state: 'detected', value: 'Engineer' },
        required_skills: {
          state: 'detected',
          items: ['TypeScript', { signal_id: 'vue', label: 'Vue' }],
        },
        nice_to_have_skills: { state: 'detected', items: ['PostgreSQL'] },
        responsibilities: { state: 'absent', items: [] },
        keywords: { state: 'detected', items: ['accessibility'] },
        seniority: { state: 'unknown', value: null },
        soft_skills: { state: 'absent', items: [] },
        domain_context: { state: 'absent', items: [] },
      },
    })

    expect(analysis.signals.required_skills.items).toEqual([
      'TypeScript',
      { signal_id: 'vue', label: 'Vue' },
    ])
  })
})
