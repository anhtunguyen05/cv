import { nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import { SelectRoot } from 'reka-ui'
import { describe, expect, it } from 'vitest'
import Button from '@/shared/components/ui/button/Button.vue'
import Input from '@/shared/components/ui/input/Input.vue'
import Label from '@/shared/components/ui/label/Label.vue'
import Select from '@/shared/components/ui/select/Select.vue'
import Textarea from '@/shared/components/ui/textarea/Textarea.vue'

describe('shared UI control contracts', () => {
  it('keeps Input controlled and forwards native attributes', async () => {
    const wrapper = mount(Input, {
      props: { modelValue: 'initial', error: true },
      attrs: {
        id: 'email',
        'aria-describedby': 'email-error',
        'aria-invalid': 'true',
        autocomplete: 'email',
      },
    })

    expect(wrapper.get('input').element.value).toBe('initial')
    expect(wrapper.get('input').attributes()).toMatchObject({
      id: 'email',
      'aria-describedby': 'email-error',
      'aria-invalid': 'true',
      autocomplete: 'email',
    })

    await wrapper.setProps({ modelValue: 'parent value' })
    expect(wrapper.get('input').element.value).toBe('parent value')
    await wrapper.get('input').setValue('updated')
    expect(wrapper.emitted('update:modelValue')).toEqual([['updated']])
  })

  it('keeps Textarea controlled and forwards multiline attributes', async () => {
    const wrapper = mount(Textarea, {
      props: { modelValue: 'draft' },
      attrs: { id: 'summary', rows: 4, maxlength: 4000 },
    })

    expect(wrapper.get('textarea').element.value).toBe('draft')
    expect(wrapper.get('textarea').attributes()).toMatchObject({
      id: 'summary',
      rows: '4',
      maxlength: '4000',
    })

    await wrapper.setProps({ modelValue: null })
    expect(wrapper.get('textarea').element.value).toBe('')
    await wrapper.get('textarea').setValue('updated draft')
    expect(wrapper.emitted('update:modelValue')).toEqual([['updated draft']])
  })

  it('preserves Label for and required attributes', () => {
    const wrapper = mount(Label, {
      props: { required: true },
      attrs: { for: 'email', 'aria-label': 'Email field' },
      slots: { default: 'Email' },
    })

    expect(wrapper.get('label').attributes()).toMatchObject({
      for: 'email',
      'aria-label': 'Email field',
    })
    expect(wrapper.get('label').text()).toContain('Email')
    expect(wrapper.get('[aria-hidden="true"]').text()).toBe('*')
  })

  it('forwards Select trigger identity and ARIA attributes', () => {
    const wrapper = mount(Select, {
      props: {
        modelValue: 'one',
        error: true,
        options: [
          { value: 'one', label: 'One' },
          { value: 'two', label: 'Two', disabled: true },
        ],
      },
      attrs: { id: 'choice', 'aria-describedby': 'choice-hint', 'aria-invalid': 'true' },
    })

    const trigger = wrapper.get('button')
    expect(trigger.attributes()).toMatchObject({
      id: 'choice',
      'aria-describedby': 'choice-hint',
      'aria-invalid': 'true',
    })
    expect(trigger.classes()).toContain('border-danger')
  })

  it('forwards Select value updates from the Reka root and preserves disabled option metadata', async () => {
    const wrapper = mount(Select, {
      props: {
        options: [
          { value: 'one', label: 'One' },
          { value: 'two', label: 'Two', disabled: true },
        ],
      },
    })

    await wrapper.findComponent(SelectRoot).vm.$emit('update:modelValue', 'one')
    await nextTick()
    expect(wrapper.emitted('update:modelValue')).toEqual([['one']])
    expect(wrapper.props('options')).toEqual([
      { value: 'one', label: 'One' },
      { value: 'two', label: 'Two', disabled: true },
    ])
  })

  it('provides a text-compatible Button variant without changing loading behavior', () => {
    const wrapper = mount(Button, {
      props: { variant: 'link', loading: true },
      attrs: { 'aria-label': 'Retry' },
      slots: { default: 'Retry' },
    })

    expect(wrapper.get('button').classes()).toContain('underline-offset-2')
    expect(wrapper.get('button').attributes()).toMatchObject({
      disabled: '',
      'aria-label': 'Retry',
    })
    expect(wrapper.text()).toContain('Loading')
  })
})
