export const jdQueryKeys = {
  all: ['job-descriptions'] as const,
  listRoot: () => [...jdQueryKeys.all, 'list'] as const,
  list: (page = 1, perPage = 20) => [...jdQueryKeys.all, 'list', page, perPage] as const,
  detail: (id: string) => [...jdQueryKeys.all, 'detail', id] as const,
  analysisRoot: (id: string) => [...jdQueryKeys.detail(id), 'analysis'] as const,
  analysis: (id: string, analysisId: string) =>
    [...jdQueryKeys.analysisRoot(id), analysisId] as const,
}
