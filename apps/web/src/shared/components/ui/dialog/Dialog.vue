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
      <DialogOverlay
        class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs transition-opacity duration-200"
      />
      <DialogContent
        class="fixed left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-6 shadow-xl border border-border focus:outline-hidden transition-all duration-200"
      >
        <div class="flex items-start justify-between gap-4 mb-4">
          <div>
            <DialogTitle v-if="title" class="text-lg font-semibold text-text">
              {{ title }}
            </DialogTitle>
            <DialogDescription v-if="description" class="text-sm text-text-muted mt-1">
              {{ description }}
            </DialogDescription>
          </div>
          <DialogClose
            class="rounded-md p-1.5 text-text-muted hover:text-text hover:bg-surface-muted transition-colors"
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
