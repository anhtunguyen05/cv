import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import type { CvPreview } from '@/features/cv/types/cv.types'

const preview: CvPreview = {
  cv_version_id: '01J00000000000000000000000',
  template_id: '01J00000000000000000000001',
  template_version: '1.0.0',
  template_name: 'Clean Modern',
  renderer_version: '1.0.0',
  version_name: 'Reviewed Version',
  snapshot_schema_version: '1.0',
  source_profile_revision: 1,
  rendered_at: null,
  sections: [
    {
      key: 'identity',
      data: {
        title: 'Backend CV',
        personal_information: {
          full_name: '<script>alert(1)</script>',
          headline: 'Engineer',
          email: 'person@example.test',
          phone: null,
          location: 'Remote',
          website_url: 'javascript:alert(1)',
          linkedin_url: 'https://linkedin.com/in/person',
          github_url: null,
        },
      },
    },
    {
      key: 'projects',
      data: [{ id: 'project-1', name: 'Safe project', url: 'javascript:alert(1)', highlights: [] }],
    },
    {
      key: 'summary',
      data: 'A trusted summary.',
    },
    { key: 'experience', data: [] },
  ],
}

describe('CvPreviewDocument', () => {
  it('renders the approved order, inert text, and only safe links', () => {
    const wrapper = mount(CvPreviewDocument, { props: { preview } })

    expect(wrapper.find('h1').text()).toContain('<script>alert(1)</script>')
    expect(wrapper.html()).not.toContain('<script>alert(1)</script>')
    expect(wrapper.findAll('h2').map((heading) => heading.text())).toEqual(['Projects', 'Summary'])
    expect(wrapper.findAll('a')).toHaveLength(1)
    expect(wrapper.find('a').attributes('href')).toBe('https://linkedin.com/in/person')
    expect(wrapper.text()).toContain('Safe project')
    expect(wrapper.text()).not.toContain('Experience')
  })
})
