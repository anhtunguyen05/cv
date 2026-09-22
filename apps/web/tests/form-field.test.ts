import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import FormField from '@/shared/components/molecules/FormField.vue'

describe('FormField accessibility', () => {
  it('links an error to the labelled control', () => {
    const wrapper = mount(FormField, {
      props: { label: 'Email', htmlFor: 'email', error: 'Email is already used' },
      slots: { default: '<input id="email" />' },
    })

    expect(wrapper.find('#email').attributes('id')).toBe('email')
    expect(wrapper.find('#email-error').text()).toContain('Email is already used')
  })
})
