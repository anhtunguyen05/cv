<script setup lang="ts">
interface Props {
  required?: boolean
}

withDefaults(defineProps<Props>(), {
  required: false,
})
</script>

<template>
  <label class="block text-sm font-medium text-[#0f172a]">
    <slot />
    <span v-if="required" class="text-[#ef4444] ml-0.5" aria-hidden="true">*</span>
  </label>
</template>
