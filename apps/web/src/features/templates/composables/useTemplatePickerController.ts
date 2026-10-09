import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCvVersionsPageQuery } from '@/features/cv/api/cv.queries'
import { ROUTES } from '@/shared/constants/routes'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'
import { useTemplatesQuery } from '../api/templates.queries'
import type { CvTemplate } from '../types/template.types'

export function useTemplatePickerController() {
  const router = useRouter()
  const route = useRoute()
  const search = ref('')
  const selected = ref<CvTemplate | null>(null)
  const versionId = computed(() =>
    typeof route.params.versionId === 'string' ? route.params.versionId : '',
  )
  const versionPage = ref(1)
  const templatesQuery = useTemplatesQuery(computed(() => Boolean(versionId.value)))
  const versionsQuery = useCvVersionsPageQuery(
    versionPage,
    undefined,
    computed(() => !versionId.value),
  )
  const versionNavigation = usePageNavigation({
    page: versionPage,
    lastPage: () => versionsQuery.data.value?.lastPage,
    isFetching: versionsQuery.isFetching,
  })
  const templates = computed(() => templatesQuery.data.value ?? [])
  const savedVersions = computed(() => versionsQuery.data.value?.items ?? [])
  const filtered = computed(() => {
    const query = search.value.toLowerCase().trim()
    return query
      ? templates.value.filter((template) =>
          `${template.name} ${template.description ?? ''} ${template.version}`
            .toLowerCase()
            .includes(query),
        )
      : templates.value
  })

  watch(
    templates,
    (available) => {
      const current = selected.value
      selected.value = current
        ? (available.find(
            (template) => template.id === current.id && template.version === current.version,
          ) ??
          available[0] ??
          null)
        : (available[0] ?? null)
    },
    { immediate: true },
  )

  function onSelect(template: CvTemplate): void {
    selected.value =
      selected.value?.id === template.id && selected.value.version === template.version
        ? null
        : template
  }

  function applyTemplate(): void {
    if (!selected.value || !versionId.value) return
    const query = new URLSearchParams({
      template_id: selected.value.id,
      template_version: selected.value.version,
    })
    void router.push(
      `${ROUTES.CV_VERSION_PREVIEW(encodeURIComponent(versionId.value))}?${query.toString()}`,
    )
  }

  function openDashboard(): void {
    void router.push(ROUTES.DASHBOARD)
  }

  function resetSearchOrOpenDashboard(): void {
    if (search.value) search.value = ''
    else openDashboard()
  }

  return {
    search,
    selected,
    versionId,
    templatesQuery,
    versionsQuery,
    savedVersions,
    versionNavigation,
    filtered,
    onSelect,
    applyTemplate,
    openDashboard,
    resetSearchOrOpenDashboard,
  }
}
