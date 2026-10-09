import { computed, ref, toValue, watch, type MaybeRefOrGetter, type Ref } from 'vue'

export interface PageNavigationOptions {
  initialPage?: number
  page?: Ref<number>
  lastPage?: MaybeRefOrGetter<number | null | undefined>
  isFetching?: MaybeRefOrGetter<boolean>
}

function normalizePage(value: number | null | undefined): number {
  return Number.isFinite(value) && value !== undefined && value !== null && value > 0
    ? Math.floor(value)
    : 1
}

export function usePageNavigation(options: PageNavigationOptions = {}) {
  const page = options.page ?? ref(normalizePage(options.initialPage ?? 1))
  const isFetching = computed(() => Boolean(toValue(options.isFetching ?? false)))
  const lastPage = computed(() => normalizePage(toValue(options.lastPage ?? 1)))

  const hasPreviousPage = computed(() => page.value > 1)
  const hasNextPage = computed(() => page.value < lastPage.value)

  function setPage(nextPage: number): void {
    page.value = Math.max(1, Math.min(normalizePage(nextPage), lastPage.value))
  }

  function previous(): void {
    if (hasPreviousPage.value) page.value -= 1
  }

  function next(): void {
    if (hasNextPage.value) page.value += 1
  }

  function reconcile(): void {
    if (!isFetching.value && page.value > lastPage.value) page.value = lastPage.value
  }

  // Query metadata can briefly describe the previous page while a new request is active.
  // Reconcile only after fetching settles so an in-flight request is never redirected.
  watch([lastPage, isFetching], reconcile, { immediate: true })

  return {
    page,
    lastPage,
    hasPreviousPage,
    hasNextPage,
    setPage,
    previous,
    next,
    reconcile,
  }
}
