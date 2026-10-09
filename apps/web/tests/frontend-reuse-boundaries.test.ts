import { nextTick, ref } from 'vue'
import { describe, expect, it } from 'vitest'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'

describe('page navigation contract', () => {
  it('keeps navigation within the first and last page boundaries', () => {
    const page = ref(1)
    const navigation = usePageNavigation({ page, lastPage: ref(3) })

    navigation.previous()
    expect(page.value).toBe(1)
    navigation.next()
    navigation.next()
    navigation.next()
    expect(page.value).toBe(3)
    navigation.setPage(99)
    expect(page.value).toBe(3)
    navigation.setPage(0)
    expect(page.value).toBe(1)
  })

  it('treats missing or zero metadata as one page', () => {
    const page = ref(1)
    const navigation = usePageNavigation({ page, lastPage: ref(0) })

    expect(navigation.lastPage.value).toBe(1)
    expect(navigation.hasPreviousPage.value).toBe(false)
    expect(navigation.hasNextPage.value).toBe(false)
  })

  it('waits for an active request to settle before reconciling a smaller last page', async () => {
    const page = ref(4)
    const lastPage = ref(4)
    const isFetching = ref(false)
    const navigation = usePageNavigation({ page, lastPage, isFetching })

    isFetching.value = true
    lastPage.value = 2
    await nextTick()
    expect(page.value).toBe(4)

    isFetching.value = false
    await nextTick()
    expect(page.value).toBe(2)
    expect(navigation.hasNextPage.value).toBe(false)
  })
})
