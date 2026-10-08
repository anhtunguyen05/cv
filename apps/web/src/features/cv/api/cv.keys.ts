export const cvQueryKeys = {
  all: ['cv'] as const,
  profiles: () => [...cvQueryKeys.all, 'profiles'] as const,
  profilesPage: (page: number, perPage = 20) =>
    [...cvQueryKeys.profiles(), 'page', page, perPage] as const,
  profile: (id: string) => [...cvQueryKeys.profiles(), id] as const,
  versionsRoot: () => [...cvQueryKeys.all, 'versions'] as const,
  versions: (profileId?: string) => [...cvQueryKeys.versionsRoot(), profileId ?? 'all'] as const,
  versionsPage: (page: number, profileId?: string) =>
    [...cvQueryKeys.versions(profileId), 'page', page] as const,
  version: (id: string) => [...cvQueryKeys.all, 'version', id] as const,
  preview: (versionId: string, templateId: string, templateVersion: string) =>
    [...cvQueryKeys.version(versionId), 'preview', templateId, templateVersion] as const,
}
