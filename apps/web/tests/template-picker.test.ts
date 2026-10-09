import { computed, nextTick, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'

const controllerState = vi.hoisted(() => ({
  controller: undefined as Record<string, unknown> | undefined,
  applyTemplate: undefined as ReturnType<typeof vi.fn> | undefined,
}))

vi.mock('@/features/templates/composables/useTemplatePickerController', () => {
  const search = ref('')
  const selected = ref({
    id: 'modern',
    name: 'Modern',
    description: 'A modern CV',
    version: '1.0.0',
  })
  const versionId = ref('version-1')
  const templates = [
    selected.value,
    {
      id: 'classic',
      name: 'Classic',
      description: 'A classic CV',
      version: '1.0.0',
    },
  ]
  const controller = {
    search,
    selected,
    versionId,
    templatesQuery: {
      isLoading: ref(false),
      isError: ref(false),
      refetch: vi.fn(),
    },
    versionsQuery: {
      isLoading: ref(false),
      isError: ref(false),
      refetch: vi.fn(),
    },
    savedVersions: ref([]),
    versionNavigation: {
      page: ref(1),
      lastPage: ref(1),
      previous: vi.fn(),
      next: vi.fn(),
    },
    filtered: computed(() => {
      const query = search.value.toLowerCase().trim()
      return query
        ? templates.filter((template) => template.name.toLowerCase().includes(query))
        : templates
    }),
    onSelect: vi.fn(),
    applyTemplate: vi.fn(),
    openDashboard: vi.fn(),
    resetSearchOrOpenDashboard: vi.fn(),
  }
  controllerState.controller = controller
  controllerState.applyTemplate = controller.applyTemplate
  return { useTemplatePickerController: () => controller }
})

import TemplatePicker from '@/features/templates/components/TemplatePicker.vue'

describe('TemplatePicker feature screen', () => {
  it('filters templates and keeps the selected preview action wired to the controller', async () => {
    const wrapper = mount(TemplatePicker, {
      global: {
        stubs: {
          RouterLink: { props: { to: String }, template: '<a :href="to"><slot /></a>' },
          Button: {
            props: { disabled: Boolean },
            template: '<button :disabled="disabled"><slot /></button>',
          },
          SearchInput: {
            props: { modelValue: String },
            emits: ['update:modelValue'],
            template:
              '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
          },
          TemplateCard: {
            props: { template: Object },
            template: '<div class="template-card">{{ template.name }}</div>',
          },
        },
      },
    })

    expect(wrapper.findAll('.template-card')).toHaveLength(2)
    await wrapper.get('input').setValue('classic')
    await nextTick()
    expect(wrapper.findAll('.template-card')).toHaveLength(1)
    expect(wrapper.get('.template-card').text()).toBe('Classic')

    await wrapper.find('button').trigger('click')
    expect(controllerState.applyTemplate).toHaveBeenCalledTimes(1)
  })
})
