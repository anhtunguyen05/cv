<script setup lang="ts">
// FormField: label above → input slot → optional helper → error below
// Always use this wrapper for all form fields.
interface Props {
  label: string
  error?: string
  hint?: string
  required?: boolean
  htmlFor?: string
}

withDefaults(defineProps<Props>(), {
  error: '',
  hint: '',
  required: false,
})
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label
      :for="htmlFor"
      class="block text-sm font-medium text-text"
    >
      {{ label }}
      <span v-if="required" class="text-danger ml-0.5" aria-hidden="true">*</span>
    </label>

    <slot />

    <p v-if="hint && !error" class="text-xs text-text-muted">{{ hint }}</p>
    <p v-if="error" class="text-xs text-danger" role="alert">{{ error }}</p>
  </div>
</template>
