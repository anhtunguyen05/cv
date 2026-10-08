export const templateQueryKeys = {
  all: ['templates'] as const,
  list: () => [...templateQueryKeys.all, 'list'] as const,
  versionEntry: () => [...templateQueryKeys.all, 'version-entry'] as const,
}
