<script setup lang="ts">
import { computed, useAttrs } from 'vue'

interface Props {
  modelValue?: string | null
  error?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  error: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const attrs = useAttrs()
const nativeValue = computed(() =>
  props.modelValue === undefined
    ? (attrs.value as string | undefined)
    : (props.modelValue ?? undefined),
)
const nativeDisabled = computed(() => props.disabled || Boolean(attrs.disabled) || undefined)
const nativeAriaInvalid = computed(
  () =>
    props.error || (attrs['aria-invalid'] as boolean | 'true' | 'false' | undefined) || undefined,
)
</script>

<template>
  <input
    v-bind="$attrs"
    :value="nativeValue"
    :disabled="nativeDisabled"
    :aria-invalid="nativeAriaInvalid"
    @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    :class="[
      'w-full h-10 px-3.5 rounded-lg text-sm text-text placeholder:text-text-subtle',
      'border transition-[border-color,box-shadow,background-color] duration-150',
      'focus:outline-none focus:ring-2 focus:ring-offset-0',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-surface',
      error
        ? 'border-danger focus:ring-danger/30 bg-danger-muted'
        : 'border-border bg-white focus:border-primary focus:ring-primary/20 hover:border-border-hover',
    ]"
  />
</template>
