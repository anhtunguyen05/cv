<script setup lang="ts">
import { computed, useAttrs } from 'vue'
import {
  SelectRoot,
  SelectTrigger,
  SelectValue,
  SelectPortal,
  SelectContent,
  SelectViewport,
  SelectItem,
  SelectItemText,
  SelectItemIndicator,
} from 'reka-ui'
import { ChevronDown, Check } from 'lucide-vue-next'

defineOptions({ inheritAttrs: false })

export interface SelectOption {
  value: string
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: string
  defaultValue?: string
  placeholder?: string
  options: SelectOption[]
  disabled?: boolean
  error?: boolean
  clearable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  defaultValue: undefined,
  placeholder: 'Select an option',
  disabled: false,
  error: false,
  clearable: false,
})

const attrs = useAttrs()
const placeholderValue = '__select_placeholder__'
const displayedOptions = computed(() =>
  props.clearable
    ? [{ value: placeholderValue, label: props.placeholder }, ...props.options]
    : props.options,
)
const triggerAriaInvalid = computed(
  () =>
    props.error || (attrs['aria-invalid'] as boolean | 'true' | 'false' | undefined) || undefined,
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <SelectRoot
    :model-value="modelValue || undefined"
    :default-value="defaultValue"
    :disabled="disabled"
    @update:model-value="
      emit('update:modelValue', ($event as string) === placeholderValue ? '' : ($event as string))
    "
  >
    <SelectTrigger
      v-bind="$attrs"
      :aria-invalid="triggerAriaInvalid"
      :class="[
        'inline-flex items-center justify-between gap-2 h-9 px-3 w-full rounded-md border bg-white text-sm text-text hover:bg-surface focus:outline-hidden focus:ring-1 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer',
        props.error
          ? 'border-danger focus:border-danger focus:ring-danger/30'
          : 'border-border focus:border-primary focus:ring-primary',
      ]"
    >
      <SelectValue :placeholder="placeholder" />
      <ChevronDown :size="14" :stroke-width="1.5" class="text-text-muted" />
    </SelectTrigger>

    <SelectPortal>
      <SelectContent
        class="z-50 min-w-[8rem] overflow-hidden rounded-md border border-border bg-white shadow-md animate-in fade-in-80"
        position="popper"
        :side-offset="4"
      >
        <SelectViewport class="p-1">
          <SelectItem
            v-for="opt in displayedOptions"
            :key="opt.value"
            :value="opt.value"
            :disabled="opt.disabled"
            class="relative flex items-center justify-between px-3 py-1.5 text-sm text-text rounded-sm select-none cursor-pointer data-[highlighted]:bg-primary-muted data-[highlighted]:text-primary-dark data-[disabled]:opacity-50 data-[disabled]:pointer-events-none outline-hidden"
          >
            <SelectItemText>{{ opt.label }}</SelectItemText>
            <SelectItemIndicator class="ml-2 text-primary">
              <Check :size="14" :stroke-width="1.5" />
            </SelectItemIndicator>
          </SelectItem>
        </SelectViewport>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>
