<script setup lang="ts">
import { TabsRoot, TabsList, TabsTrigger, TabsContent } from 'reka-ui'

interface TabItem {
  value: string
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: string
  defaultValue?: string
  items: TabItem[]
}

defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <TabsRoot
    :model-value="modelValue"
    :default-value="defaultValue ?? items[0]?.value"
    class="w-full flex flex-col"
    @update:model-value="emit('update:modelValue', $event as string)"
  >
    <TabsList class="flex items-center gap-2 border-b border-border pb-px">
      <TabsTrigger
        v-for="item in items"
        :key="item.value"
        :value="item.value"
        :disabled="item.disabled"
        class="px-3.5 py-2 text-sm font-medium text-text-muted border-b-2 border-transparent transition-all hover:text-text data-[state=active]:text-primary data-[state=active]:border-primary disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
      >
        {{ item.label }}
      </TabsTrigger>
    </TabsList>

    <div class="mt-4">
      <TabsContent
        v-for="item in items"
        :key="item.value"
        :value="item.value"
        class="focus:outline-hidden"
      >
        <slot :name="item.value" />
      </TabsContent>
    </div>
  </TabsRoot>
</template>
