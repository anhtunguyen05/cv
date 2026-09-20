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
      class="absolute left-3.5 text-text-subtle pointer-events-none"
      :size="18"
      :stroke-width="1.5"
    />
    <input
      v-model="inputValue"
      type="search"
      :placeholder="placeholder"
      class="w-full h-10 sm:h-11 pl-10 pr-9 text-sm rounded-xl border border-border bg-white placeholder:text-text-subtle focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all"
    />
    <button
      v-if="inputValue"
      type="button"
      class="absolute right-3.5 text-text-subtle hover:text-text-muted transition-colors p-1"
      aria-label="Clear search"
      @click="clear"
    >
      <X :size="15" :stroke-width="1.5" />
    </button>
  </div>
</template>
