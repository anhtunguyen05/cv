<script setup lang="ts">
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
}

withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  defaultValue: undefined,
  placeholder: 'Select an option',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <SelectRoot
    :model-value="modelValue"
    :default-value="defaultValue"
    :disabled="disabled"
    @update:model-value="emit('update:modelValue', $event as string)"
  >
    <SelectTrigger
      class="inline-flex items-center justify-between gap-2 h-9 px-3 w-full rounded-md border border-[#e2e8f0] bg-white text-sm text-[#0f172a] hover:bg-[#f8fafc] focus:outline-hidden focus:border-[#6366f1] focus:ring-1 focus:ring-[#6366f1] disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
    >
      <SelectValue :placeholder="placeholder" />
      <ChevronDown :size="14" :stroke-width="1.5" class="text-[#64748b]" />
    </SelectTrigger>

    <SelectPortal>
      <SelectContent
        class="z-50 min-w-[8rem] overflow-hidden rounded-md border border-[#e2e8f0] bg-white shadow-md animate-in fade-in-80"
        position="popper"
        :side-offset="4"
      >
        <SelectViewport class="p-1">
          <SelectItem
            v-for="opt in options"
            :key="opt.value"
            :value="opt.value"
            :disabled="opt.disabled"
            class="relative flex items-center justify-between px-3 py-1.5 text-sm text-[#0f172a] rounded-sm select-none cursor-pointer data-[highlighted]:bg-[#eef2ff] data-[highlighted]:text-[#4338ca] data-[disabled]:opacity-50 data-[disabled]:pointer-events-none outline-hidden"
          >
            <SelectItemText>{{ opt.label }}</SelectItemText>
            <SelectItemIndicator class="ml-2 text-[#6366f1]">
              <Check :size="14" :stroke-width="1.5" />
            </SelectItemIndicator>
          </SelectItem>
        </SelectViewport>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>
