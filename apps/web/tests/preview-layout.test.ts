import { nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import PreviewLayout from '@/app/layouts/PreviewLayout.vue'

describe('PreviewLayout print readiness', () => {
  afterEach(() => vi.restoreAllMocks())

  it('keeps print controls guarded until the exact preview is ready', async () => {
    const wrapper = mount(PreviewLayout, {
      global: {
        stubs: { RouterLink: { template: '<a><slot /></a>' } },
      },
    })
    const buttons = () => wrapper.findAll('button')

    expect(buttons().every((button) => button.attributes('disabled') !== undefined)).toBe(true)

    window.dispatchEvent(
      new CustomEvent('careerfitcv:preview-state', {
        detail: { ready: true, title: 'Reviewed Version - Clean Modern' },
      }),
    )
    await nextTick()
    expect(buttons().every((button) => button.attributes('disabled') === undefined)).toBe(true)

    const print = vi.spyOn(window, 'print').mockImplementation(() => undefined)
    await buttons()[1]?.trigger('click')
    expect(print).toHaveBeenCalledTimes(1)
    expect(document.title).toBe('Reviewed Version - Clean Modern')
    expect(wrapper.get('[role="status"]').text()).toContain('cannot be observed')
  })
})
