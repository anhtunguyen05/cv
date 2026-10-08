<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { Search, X } from 'lucide-vue-next'

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
let pendingSearch: ReturnType<typeof setTimeout> | undefined

function cancelPendingSearch(): void {
  if (pendingSearch !== undefined) clearTimeout(pendingSearch)
  pendingSearch = undefined
}

function scheduleSearch(value: string): void {
  cancelPendingSearch()
  pendingSearch = setTimeout(() => {
    emit('update:modelValue', value)
    emit('search', value)
    pendingSearch = undefined
  }, 350)
}

watch(
  () => props.modelValue,
  (value) => {
    cancelPendingSearch()
    if (value !== inputValue.value) inputValue.value = value
  },
)

function onInput(event: Event): void {
  const value = (event.target as HTMLInputElement).value
  inputValue.value = value
  scheduleSearch(value)
}

function clear() {
  inputValue.value = ''
  scheduleSearch('')
}

onBeforeUnmount(cancelPendingSearch)
</script>

<template>
  <div class="relative flex items-center">
    <Search
      class="absolute left-3.5 text-text-subtle pointer-events-none"
      :size="18"
      :stroke-width="1.5"
    />
    <input
      :value="inputValue"
      type="search"
      :placeholder="placeholder"
      class="w-full h-10 sm:h-11 pl-10 pr-9 text-sm rounded-xl border border-border bg-white placeholder:text-text-subtle focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all"
      @input="onInput"
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
