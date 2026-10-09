import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import PaginationNav from '@/shared/components/PaginationNav.vue'
import CvVersionSelector from '@/features/cv/components/CvVersionSelector.vue'
import type { CvVersionSummary } from '@/features/cv/types/cv.types'

const ButtonStub = {
  props: {
    disabled: Boolean,
    size: String,
  },
  template: '<button :disabled="disabled" :data-size="size"><slot /></button>',
}

const RouterLinkStub = {
  props: { to: String },
  template: '<a :href="to"><slot /></a>',
}

const versions: CvVersionSummary[] = [
  {
    id: 'version-1',
    name: 'Baseline',
    source_profile_id: 'profile-1',
    source_profile_revision: 3,
    created_at: '2026-10-09T00:00:00Z',
  },
]

describe('PaginationNav', () => {
  it('renders labels, disabled boundaries, default md buttons, and a live page indicator', async () => {
    const wrapper = mount(PaginationNav, {
      props: { page: 1, lastPage: 3, label: 'Report pages' },
      global: { stubs: { Button: ButtonStub } },
    })

    const buttons = wrapper.findAll('button')
    expect(buttons).toHaveLength(2)
    expect(buttons[0].text()).toBe('Previous')
    expect(buttons[1].text()).toBe('Next')
    expect(buttons[0].attributes('disabled')).toBeDefined()
    expect(buttons[1].attributes('disabled')).toBeUndefined()
    expect(buttons[0].attributes('data-size')).toBe('md')
    expect(wrapper.get('[aria-live="polite"]').text()).toBe('Page 1 of 3')

    await buttons[1].trigger('click')
    expect(wrapper.emitted('next')).toHaveLength(1)
  })

  it('preserves the text-link pagination appearance and its custom labels', () => {
    const wrapper = mount(PaginationNav, {
      props: {
        page: 2,
        lastPage: 3,
        label: 'CV Version pages',
        previousLabel: 'Previous versions',
        nextLabel: 'Next versions',
        appearance: 'text',
      },
      global: { stubs: { Button: ButtonStub } },
    })

    const buttons = wrapper.findAll('button')
    expect(buttons[0].classes()).toContain('underline')
    expect(buttons[1].classes()).toContain('underline')
    expect(buttons[0].text()).toBe('Previous versions')
    expect(buttons[1].text()).toBe('Next versions')
    expect(wrapper.findComponent(ButtonStub).exists()).toBe(false)
  })
})

describe('CvVersionSelector', () => {
  it('renders the preview variant destination and forwards pager events', async () => {
    const destination = vi.fn((version: CvVersionSummary) => `/templates/${version.id}`)
    const wrapper = mount(CvVersionSelector, {
      props: { versions, page: 1, lastPage: 2, destination },
      global: { stubs: { Button: ButtonStub, RouterLink: RouterLinkStub } },
    })

    expect(wrapper.text()).toContain('Choose template')
    expect(wrapper.get('a').attributes('href')).toBe('/templates/version-1')
    await wrapper.get('[aria-label="Saved Version pages"] button:last-child').trigger('click')
    expect(wrapper.emitted('next')).toHaveLength(1)
  })

  it('renders the card variant with the same destination and previous pager event', async () => {
    const destination = vi.fn((version: CvVersionSummary) => `/templates/${version.id}`)
    const wrapper = mount(CvVersionSelector, {
      props: { versions, page: 2, lastPage: 2, variant: 'card', destination },
      global: { stubs: { Button: ButtonStub, RouterLink: RouterLinkStub } },
    })

    expect(wrapper.text()).toContain('Saved 2026-10-09T00:00:00Z')
    expect(wrapper.text()).not.toContain('Choose template')
    expect(wrapper.get('a').attributes('href')).toBe('/templates/version-1')
    expect(
      wrapper.get('[aria-label="Saved Version pages"] button:last-child').attributes('disabled'),
    ).toBeDefined()
    await wrapper.get('[aria-label="Saved Version pages"] button:first-child').trigger('click')
    expect(wrapper.emitted('previous')).toHaveLength(1)
  })
})
