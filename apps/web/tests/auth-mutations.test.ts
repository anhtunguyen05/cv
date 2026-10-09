import { beforeEach, describe, expect, it, vi } from 'vitest'

const testState = vi.hoisted(() => ({
  authStore: {
    setUser: vi.fn(),
    clearAuth: vi.fn(),
  },
  router: {
    currentRoute: { value: { query: { return_to: '/cv/1/edit' } } },
    push: vi.fn().mockResolvedValue(undefined),
  },
  queryClient: {
    cancelQueries: vi.fn().mockResolvedValue(undefined),
    clear: vi.fn(),
  },
  api: {
    getMe: vi.fn(),
    login: vi.fn(),
    register: vi.fn(),
    logout: vi.fn(),
  },
  mutation: vi.fn((options: Record<string, unknown>) => options),
}))

vi.mock('@tanstack/vue-query', () => ({ useMutation: testState.mutation }))

vi.mock('@/features/auth/api/auth.api', () => testState.api)

vi.mock('@/features/auth/stores/auth.store', () => ({
  useAuthStore: () => testState.authStore,
}))

vi.mock('vue-router', () => ({
  useRouter: () => testState.router,
}))

vi.mock('@/app/providers/vue-query', () => ({
  queryClient: testState.queryClient,
}))

vi.mock('@/shared/api/client', () => ({
  ApiRequestError: class ApiRequestError extends Error {
    constructor(readonly status: number) {
      super('Request failed')
    }
  },
}))

const user = { id: '1', name: 'Nguyen Anh Tu', email: 'tu@example.com' }

describe('auth mutation orchestration', () => {
  beforeEach(() => {
    testState.authStore.setUser.mockReset()
    testState.authStore.clearAuth.mockReset()
    testState.router.currentRoute.value.query.return_to = '/cv/1/edit'
    testState.router.push.mockReset().mockResolvedValue(undefined)
    testState.queryClient.cancelQueries.mockReset().mockResolvedValue(undefined)
    testState.queryClient.clear.mockReset()
    Object.values(testState.api).forEach((mock) => mock.mockReset())
  })

  it.each(['/cv/1/edit', '//external.example', '/\\external.example'])(
    'keeps login navigation safe for return_to=%s',
    async (returnTo) => {
      testState.router.currentRoute.value.query.return_to = returnTo
      const { useLoginMutation } = await import('@/features/auth/composables/useAuthMutations')
      const mutation = useLoginMutation() as unknown as {
        onSuccess: (data: { data: { user: typeof user } }) => unknown
      }

      await mutation.onSuccess({ data: { user } })

      expect(testState.authStore.setUser).toHaveBeenCalledWith(user)
      expect(testState.router.push).toHaveBeenCalledWith(
        returnTo === '/cv/1/edit' ? returnTo : '/dashboard',
      )
    },
  )

  it('reconciles a registration transport failure once through getMe', async () => {
    testState.api.getMe.mockResolvedValue(user)
    const { useRegisterMutation } = await import('@/features/auth/composables/useAuthMutations')
    const mutation = useRegisterMutation() as unknown as {
      onError: (error: Error) => unknown
    }

    await mutation.onError(new Error('Network failure'))

    expect(testState.api.getMe).toHaveBeenCalledTimes(1)
    expect(testState.authStore.setUser).toHaveBeenCalledWith(user)
    expect(testState.router.push).toHaveBeenCalledWith('/dashboard')
  })

  it('leaves known registration errors retryable without reconciliation', async () => {
    const { ApiRequestError } = await import('@/shared/api/client')
    const { useRegisterMutation } = await import('@/features/auth/composables/useAuthMutations')
    const mutation = useRegisterMutation() as unknown as {
      onError: (error: Error) => unknown
    }

    await mutation.onError(new ApiRequestError(422))

    expect(testState.api.getMe).not.toHaveBeenCalled()
    expect(testState.authStore.setUser).not.toHaveBeenCalled()
    expect(testState.router.push).not.toHaveBeenCalled()
  })

  it('clears auth and query state before navigating after logout', async () => {
    const events: string[] = []
    testState.authStore.clearAuth.mockImplementation(() => events.push('clear-auth'))
    testState.queryClient.cancelQueries.mockImplementation(() => {
      events.push('cancel-queries')
      return Promise.resolve()
    })
    testState.queryClient.clear.mockImplementation(() => events.push('clear-cache'))
    testState.router.push.mockImplementation(async () => {
      events.push('navigate')
    })
    const { useLogoutMutation } = await import('@/features/auth/composables/useAuthMutations')
    const mutation = useLogoutMutation() as unknown as {
      onSuccess: () => unknown
    }

    mutation.onSuccess()
    await new Promise((resolve) => setTimeout(resolve, 0))

    expect(events).toEqual(['clear-auth', 'cancel-queries', 'clear-cache', 'navigate'])
  })
})
