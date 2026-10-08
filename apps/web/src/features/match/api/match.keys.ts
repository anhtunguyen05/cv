export const matchQueryKeys = {
  all: ['match-reports'] as const,
  list: (page = 1, perPage = 20) => [...matchQueryKeys.all, 'list', page, perPage] as const,
  detail: (id: string) => [...matchQueryKeys.all, 'detail', id] as const,
}
