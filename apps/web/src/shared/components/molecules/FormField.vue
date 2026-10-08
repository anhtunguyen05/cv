<script setup lang="ts">
import { computed } from 'vue'
// FormField: label above → input slot → optional helper → error below
// Always use this wrapper for all form fields.
interface Props {
  label: string
  error?: string
  hint?: string
  required?: boolean
  htmlFor?: string
}

const props = withDefaults(defineProps<Props>(), {
  error: '',
  hint: '',
  required: false,
})

const errorId = computed(() =>
  props.htmlFor && props.error ? `${props.htmlFor}-error` : undefined,
)
const hintId = computed(() =>
  props.htmlFor && props.hint && !props.error ? `${props.htmlFor}-hint` : undefined,
)
const controlProps = computed(() => ({
  id: props.htmlFor,
  required: props.required || undefined,
  'aria-required': props.required || undefined,
  'aria-invalid': props.error ? true : undefined,
  'aria-describedby': errorId.value ?? hintId.value,
}))
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label :for="htmlFor" class="block text-sm font-medium text-text">
      {{ label }}
      <span v-if="required" class="text-danger ml-0.5" aria-hidden="true">*</span>
    </label>

    <slot :control-props="controlProps" />

    <p
      v-if="hint && !error"
      :id="hintId"
      class="text-xs text-text-muted"
    >
      {{ hint }}
    </p>
    <p v-if="error" :id="errorId" class="text-xs text-danger" role="alert" aria-live="polite">
      {{ error }}
    </p>
  </div>
</template>
