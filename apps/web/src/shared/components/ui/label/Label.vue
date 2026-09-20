<script setup lang="ts">
interface Props {
  required?: boolean
}

withDefaults(defineProps<Props>(), {
  required: false,
})
</script>

<template>
  <label class="block text-sm font-medium text-text">
    <slot />
    <span v-if="required" class="text-danger ml-0.5" aria-hidden="true">*</span>
  </label>
</template>
