<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next'
import Button from '@/shared/components/ui/button/Button.vue'
defineProps<{ loading: boolean }>()
defineEmits<{ confirm: [] }>()
</script>
<template>
  <Button type="button" variant="destructive" :loading :disabled="loading" @click="$emit('confirm')"
    ><Trash2 :size="15" aria-hidden="true" /> Delete</Button
  >
</template>
