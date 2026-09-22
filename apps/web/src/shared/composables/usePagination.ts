import { ref, computed } from 'vue'

export function usePagination(initialPage = 1, initialPerPage = 15) {
  const page = ref(initialPage)
  const perPage = ref(initialPerPage)
  const total = ref(0)

  const lastPage = computed(() => Math.ceil(total.value / perPage.value))
  const hasNextPage = computed(() => page.value < lastPage.value)
  const hasPrevPage = computed(() => page.value > 1)

  function nextPage() {
    if (hasNextPage.value) page.value++
  }

  function prevPage() {
    if (hasPrevPage.value) page.value--
  }

  function setPage(n: number) {
    page.value = Math.max(1, Math.min(n, lastPage.value))
  }

  function setTotal(n: number) {
    total.value = n
  }

  return {
    page,
    perPage,
    total,
    lastPage,
    hasNextPage,
    hasPrevPage,
    nextPage,
    prevPage,
    setPage,
    setTotal,
  }
}
