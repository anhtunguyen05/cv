<script setup lang="ts">
import {
  AccordionRoot,
  AccordionItem,
  AccordionHeader,
  AccordionTrigger,
  AccordionContent,
} from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'

export interface AccordionItemData {
  value: string
  title: string
  content?: string
}

interface Props {
  items: AccordionItemData[]
  type?: 'single' | 'multiple'
  collapsible?: boolean
  defaultValue?: string | string[]
}

withDefaults(defineProps<Props>(), {
  type: 'single',
  collapsible: true,
  defaultValue: undefined,
})
</script>

<template>
  <AccordionRoot
    :type="type as any"
    :collapsible="collapsible"
    :default-value="defaultValue as any"
    class="w-full divide-y divide-border border border-border rounded-lg overflow-hidden bg-white"
  >
    <AccordionItem v-for="item in items" :key="item.value" :value="item.value" class="group">
      <AccordionHeader class="flex">
        <AccordionTrigger
          class="flex flex-1 items-center justify-between py-3.5 px-4 text-sm font-medium text-text hover:bg-surface transition-all group-data-[state=open]:text-primary cursor-pointer"
        >
          <span>{{ item.title }}</span>
          <ChevronDown
            :size="16"
            :stroke-width="1.5"
            class="text-text-muted transition-transform duration-200 group-data-[state=open]:rotate-180"
          />
        </AccordionTrigger>
      </AccordionHeader>
      <AccordionContent
        class="overflow-hidden px-4 pb-4 pt-1 text-sm text-text-muted transition-all data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down"
      >
        <slot :name="item.value">
          <p>{{ item.content }}</p>
        </slot>
      </AccordionContent>
    </AccordionItem>
  </AccordionRoot>
</template>
