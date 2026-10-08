import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import FormField from '@/shared/components/molecules/FormField.vue'

describe('FormField accessibility', () => {
  it('links an error to the labelled control', () => {
    const wrapper = mount(FormField, {
      props: { label: 'Email', htmlFor: 'email', error: 'Email is already used', required: true },
      slots: { default: '<template #default="{ controlProps }"><input v-bind="controlProps" /></template>' },
    })

    expect(wrapper.find('#email').attributes('id')).toBe('email')
    expect(wrapper.find('#email').attributes('aria-invalid')).toBe('true')
    expect(wrapper.find('#email').attributes('aria-describedby')).toBe('email-error')
    expect(wrapper.find('#email').attributes('required')).toBe('')
    expect(wrapper.find('#email').attributes('aria-required')).toBe('true')
    expect(wrapper.find('#email-error').text()).toContain('Email is already used')
  })
})
