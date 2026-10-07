<script setup lang="ts">
import { onBeforeMount, onBeforeUnmount, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Printer, ArrowLeft, Sparkles, Download } from 'lucide-vue-next'
import { ROUTES } from '@/shared/constants/routes'
import AppButton from '@/shared/components/atoms/AppButton.vue'

const previewReady = ref(false)
const printMessage = ref('')
const printTitle = ref('')
const printing = ref(false)
const previewGeneration = ref(0)

function onPreviewState(event: Event): void {
  const detail = (event as CustomEvent<{ ready?: boolean; title?: string }>).detail
  previewGeneration.value += 1
  previewReady.value = detail?.ready === true
  if (detail?.title) printTitle.value = detail.title
}

async function printCV(): Promise<void> {
  if (printing.value) return
  printMessage.value = ''
  if (!previewReady.value) {
    printMessage.value = 'Wait for the exact preview to finish loading before printing.'
    return
  }
  if (typeof window.print !== 'function') {
    printMessage.value =
      'Printing is unavailable in this browser. Use a supported browser and try again.'
    return
  }
  printing.value = true
  const generation = previewGeneration.value
  try {
    try {
      if (typeof document.fonts?.ready?.then === 'function') {
        await Promise.race([
          document.fonts.ready,
          new Promise<void>((resolve) => window.setTimeout(resolve, 5000)),
        ])
      }
    } catch {
      // The bundled fallback font remains safe when FontFaceSet is unavailable.
    }
    if (generation !== previewGeneration.value || !previewReady.value) {
      printMessage.value = 'The preview changed before printing. Review it and try again.'
      return
    }
    if (printTitle.value) document.title = printTitle.value
    window.print()
    printMessage.value = 'Print requested. Save or cancel completion cannot be observed here.'
  } catch {
    printMessage.value = 'Printing is unavailable in this browser. Try the browser print menu.'
  } finally {
    printing.value = false
  }
}

// Register before child mounted hooks so a cached preview cannot emit readiness early.
onBeforeMount(() => window.addEventListener('careerfitcv:preview-state', onPreviewState))
onBeforeUnmount(() => window.removeEventListener('careerfitcv:preview-state', onPreviewState))
</script>

<template>
  <div class="min-h-[100dvh] flex flex-col bg-surface-muted">
    <header
      class="no-print sticky top-0 z-30 flex h-16 flex-shrink-0 items-center justify-between border-b border-border bg-white/95 px-6 shadow-xs backdrop-blur-md lg:px-10"
    >
      <div class="flex items-center gap-4">
        <RouterLink
          :to="ROUTES.DASHBOARD"
          class="inline-flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-medium text-text-muted transition-colors hover:bg-surface hover:text-text"
        >
          <ArrowLeft :size="16" />
          <span class="hidden sm:inline">Dashboard</span>
        </RouterLink>
        <div class="hidden h-5 w-px bg-border sm:block" />
        <div class="flex items-center gap-2.5">
          <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary shadow-2xs">
            <Sparkles :size="14" class="text-white" />
          </div>
          <span class="text-sm font-bold text-text">CV Document Preview</span>
          <span
            class="hidden rounded-full border border-success-border bg-success-muted px-2.5 py-0.5 font-mono text-xs font-medium text-success-hover md:inline"
            >A4 portrait · 16 mm</span
          >
        </div>
      </div>

      <div class="flex items-center gap-3">
        <slot name="controls" />
        <AppButton
          size="md"
          variant="outline"
          :disabled="!previewReady || printing"
          :aria-disabled="!previewReady || printing"
          @click="printCV"
        >
          <Printer :size="15" :stroke-width="1.5" />
          <span class="hidden sm:inline">Print / PDF</span>
        </AppButton>
        <AppButton
          size="md"
          :disabled="!previewReady || printing"
          :aria-disabled="!previewReady || printing"
          @click="printCV"
        >
          <Download :size="15" :stroke-width="2" />
          <span>Export</span>
        </AppButton>
      </div>
    </header>

    <p
      v-if="printMessage"
      class="no-print mx-auto mt-3 max-w-[794px] px-4 text-center text-sm text-text-muted"
      role="status"
    >
      {{ printMessage }}
    </p>
    <main class="flex-1 overflow-y-auto bg-surface-muted px-4 py-8 sm:py-12">
      <slot />
    </main>
  </div>
</template>
