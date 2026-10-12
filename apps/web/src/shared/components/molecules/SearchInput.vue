<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { Search, X } from 'lucide-vue-next'
import AppIcon from '@/shared/components/atoms/AppIcon.vue'
import Button from '@/shared/components/ui/button/Button.vue'
import Input from '@/shared/components/ui/input/Input.vue'

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

function onInput(value: string): void {
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
    <AppIcon
      :icon="Search"
      :size="18"
      class="absolute left-3.5 text-text-subtle pointer-events-none"
    />
    <Input
      :model-value="inputValue"
      type="search"
      :placeholder="placeholder"
      class="w-full h-10 sm:h-11 pl-10 pr-9 text-sm rounded-xl border border-border bg-white placeholder:text-text-subtle focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 hover:border-border-hover transition-all"
      @update:model-value="onInput"
    />
    <Button
      v-if="inputValue"
      type="button"
      variant="unstyled"
      class="absolute right-3.5 text-text-subtle hover:text-text-muted transition-colors p-1"
      aria-label="Clear search"
      @click="clear"
    >
      <AppIcon :icon="X" :size="15" />
    </Button>
  </div>
</template>
