<script setup lang="ts">
import Skeleton from '../ui/skeleton/Skeleton.vue'

interface Props {
  rows?: number
  showAvatar?: boolean
}

withDefaults(defineProps<Props>(), {
  rows: 3,
  showAvatar: false,
})
</script>

<template>
  <div class="space-y-4">
    <div
      v-for="i in rows"
      :key="i"
      class="flex gap-3 items-start p-4 border border-[#e2e8f0] rounded-lg bg-white"
    >
      <Skeleton v-if="showAvatar" class="w-9 h-9 rounded-full flex-shrink-0" />
      <div class="flex-1 space-y-2">
        <Skeleton class="h-4 w-3/5" />
        <Skeleton class="h-3 w-4/5" />
        <Skeleton class="h-3 w-2/5" />
      </div>
      <Skeleton class="h-6 w-16 rounded-full" />
    </div>
  </div>
</template>
