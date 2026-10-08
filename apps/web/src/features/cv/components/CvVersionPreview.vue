<script setup lang="ts">
import { computed, onBeforeUnmount, onErrorCaptured, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import Button from '@/shared/components/ui/button/Button.vue'
import CvPreviewDocument from '@/features/cv/components/CvPreviewDocument.vue'
import { useCvPreviewQuery } from '@/features/cv/api/cv.queries'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'

const route = useRoute()
const router = useRouter()
const versionId = computed(() => String(route.params.versionId ?? route.params.cvVersion ?? ''))
const templateId = computed(() =>
  typeof route.query.template_id === 'string' ? route.query.template_id : '',
)
const templateVersion = computed(() =>
  typeof route.query.template_version === 'string' ? route.query.template_version : '',
)
const duplicateSource = computed(
  () => Array.isArray(route.query.template_id) || Array.isArray(route.query.template_version),
)
const sourceProvided = computed(
  () => 'template_id' in route.query || 'template_version' in route.query,
)
const sourceInvalid = computed(
  () =>
    duplicateSource.value ||
    (sourceProvided.value && (!templateId.value || !templateVersion.value)),
)
const rendererFailed = ref(false)
const hasSource = computed(() =>
  Boolean(versionId.value && templateId.value && templateVersion.value),
)

const previewQuery = useCvPreviewQuery(versionId, templateId, templateVersion)

watch([versionId, templateId, templateVersion], () => {
  rendererFailed.value = false
})

const errorMessage = computed(() => {
  const error = previewQuery.error.value
  if (error instanceof ApiRequestError && error.code === 'TEMPLATE_UNAVAILABLE') {
    return 'This template is no longer available. Choose another active template.'
  }
  if (error instanceof ApiRequestError && error.code === 'RENDER_SOURCE_UNSUPPORTED') {
    return 'This saved Version uses a rendering schema that this preview does not support.'
  }
  if (error instanceof ApiRequestError && error.code === 'PREVIEW_SOURCE_INVALID') {
    return 'The saved Version could not be rendered safely.'
  }
  return 'Unable to load this saved Version preview. Try again.'
})

function templatesPath(): string {
  return ROUTES.CV_VERSION_TEMPLATES(encodeURIComponent(versionId.value))
}

function emitPreviewState(): void {
  if (typeof window === 'undefined') return
  window.dispatchEvent(
    new CustomEvent('careerfitcv:preview-state', {
      detail: {
        ready:
          Boolean(previewQuery.data.value) &&
          !previewQuery.isError.value &&
          !previewQuery.isFetching.value &&
          !rendererFailed.value,
        title: previewQuery.data.value
          ? `${previewQuery.data.value.version_name} - ${previewQuery.data.value.template_name}`
          : undefined,
      },
    }),
  )
}

watch(
  [
    hasSource,
    sourceInvalid,
    () => previewQuery.data.value,
    () => previewQuery.isError.value,
    () => previewQuery.isFetching.value,
    rendererFailed,
  ],
  ([source, invalid, preview]) => {
    if (!source && !invalid) {
      void router.replace(templatesPath())
      return
    }
    if (preview) document.title = `${preview.version_name} - ${preview.template_name}`
    emitPreviewState()
  },
  { immediate: true },
)

onErrorCaptured(() => {
  rendererFailed.value = true
  emitPreviewState()
  return false
})

onMounted(emitPreviewState)
onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('careerfitcv:preview-state', { detail: { ready: false } }))
  }
})
</script>

<template>
  <div class="flex w-full flex-col items-center">
    <div class="no-print mb-6 flex w-full max-w-[794px] items-center justify-between gap-4">
      <RouterLink :to="templatesPath()">
        <Button size="sm" variant="outline">Choose another template</Button>
      </RouterLink>
      <span v-if="previewQuery.data.value" class="text-right text-sm text-text-muted">
        Immutable Version · {{ previewQuery.data.value.version_name }}
      </span>
    </div>

    <p v-if="sourceInvalid" role="alert" class="no-print">
      The preview source is invalid. Choose a template again.
    </p>
    <p v-else-if="previewQuery.isLoading.value" role="status" class="no-print">
      Loading exact preview…
    </p>
    <div
      v-else-if="rendererFailed"
      class="no-print w-full max-w-[794px] rounded-lg border border-danger-border bg-danger-muted p-4 text-sm text-danger-text"
      role="alert"
    >
      <p>The preview could not be rendered safely.</p>
      <Button size="sm" variant="outline" class="mt-3" @click="rendererFailed = false">
        Retry render
      </Button>
    </div>
    <div
      v-else-if="previewQuery.isError.value"
      class="no-print w-full max-w-[794px] rounded-lg border border-danger-border bg-danger-muted p-4 text-sm text-danger-text"
      role="alert"
    >
      <p>{{ errorMessage }}</p>
      <div class="mt-3 flex flex-wrap gap-2">
        <Button size="sm" variant="outline" @click="previewQuery.refetch()">Retry</Button>
        <RouterLink :to="templatesPath()">
          <Button size="sm">Return to templates</Button>
        </RouterLink>
      </div>
    </div>
    <div v-else-if="previewQuery.data.value" class="overflow-x-auto pb-12">
      <CvPreviewDocument :preview="previewQuery.data.value" />
    </div>
  </div>
</template>
