<script setup lang="ts">
interface Props {
  noPadding?: boolean
}

withDefaults(defineProps<Props>(), {
  noPadding: false,
})
</script>

<template>
  <div
    :class="[
      'bg-white border border-[#e2e8f0] rounded-lg',
      'shadow-[0_1px_3px_0_rgba(0,0,0,0.04),0_1px_2px_-1px_rgba(0,0,0,0.04)]',
      !noPadding && 'p-5',
    ]"
  >
    <slot />
  </div>
</template>
