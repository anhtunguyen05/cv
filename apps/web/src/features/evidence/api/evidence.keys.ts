export const evidenceQueryKeys = {
  all: ['evidence-interviews'] as const,
  interview: (id: string) => [...evidenceQueryKeys.all, 'detail', id] as const,
  patches: ['patches'] as const,
  patch: (id: string) => [...evidenceQueryKeys.patches, id] as const,
}
