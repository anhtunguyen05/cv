<script setup lang="ts">
import {
  DialogRoot,
  DialogTrigger,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
  DialogClose,
} from 'reka-ui'
import { X } from 'lucide-vue-next'

interface Props {
  open?: boolean
  title?: string
  description?: string
}

withDefaults(defineProps<Props>(), {
  open: undefined,
  title: '',
  description: '',
})

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()
</script>

<template>
  <DialogRoot :open="open" @update:open="emit('update:open', $event)">
    <DialogTrigger as-child>
      <slot name="trigger" />
    </DialogTrigger>

    <DialogPortal>
      <DialogOverlay class="fixed inset-0 z-50 bg-[#0f172a]/40 backdrop-blur-xs transition-opacity duration-200" />
      <DialogContent
        class="fixed left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-6 shadow-xl border border-[#e2e8f0] focus:outline-hidden transition-all duration-200"
      >
        <div class="flex items-start justify-between gap-4 mb-4">
          <div>
            <DialogTitle v-if="title" class="text-lg font-semibold text-[#0f172a]">
              {{ title }}
            </DialogTitle>
            <DialogDescription v-if="description" class="text-sm text-[#64748b] mt-1">
              {{ description }}
            </DialogDescription>
          </div>
          <DialogClose
            class="rounded-md p-1.5 text-[#64748b] hover:text-[#0f172a] hover:bg-[#f1f5f9] transition-colors"
            aria-label="Close"
          >
            <X :size="16" :stroke-width="1.5" />
          </DialogClose>
        </div>

        <slot />

        <div v-if="$slots.footer" class="mt-6 flex items-center justify-end gap-3">
          <slot name="footer" />
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
