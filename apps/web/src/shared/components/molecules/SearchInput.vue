<script setup lang="ts">
import { ref } from 'vue'
import { Search, X } from 'lucide-vue-next'
import { useDebounce } from '@/shared/composables/useDebounce'

interface Props {
  modelValue?: string
  placeholder?: string
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Search...',
  loading: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  search: [value: string]
}>()

const inputValue = ref(props.modelValue)
const debounced = useDebounce(inputValue, 350)

import { watch } from 'vue'
watch(debounced, (val) => {
  emit('update:modelValue', val)
  emit('search', val)
})

function clear() {
  inputValue.value = ''
}
</script>

<template>
  <div class="relative flex items-center">
    <Search
      class="absolute left-3 text-[#94a3b8] pointer-events-none"
      :size="16"
      :stroke-width="1.5"
    />
    <input
      v-model="inputValue"
      type="search"
      :placeholder="placeholder"
      class="w-full h-9 pl-9 pr-8 text-sm rounded-md border border-[#e2e8f0] bg-white placeholder:text-[#94a3b8] focus:outline-none focus:border-[#6366f1] focus:ring-2 focus:ring-[#6366f1]/20 transition-colors"
    />
    <button
      v-if="inputValue"
      type="button"
      class="absolute right-3 text-[#94a3b8] hover:text-[#64748b] transition-colors"
      aria-label="Clear search"
      @click="clear"
    >
      <X :size="14" :stroke-width="1.5" />
    </button>
  </div>
</template>
