import { useQuery } from '@tanstack/vue-query'
import { getCvPreview, getCvProfiles, getCvProfile, getCvVersionsPage } from './cv.api'
import { cvQueryKeys } from './cv.keys'
import { computed, toValue, type MaybeRef } from 'vue'

export function useCvProfilesQuery(page: MaybeRef<number> = 1, perPage = 20) {
  return useQuery({
    queryKey: computed(() => cvQueryKeys.profilesPage(toValue(page), perPage)),
    queryFn: () => getCvProfiles(toValue(page), perPage),
    staleTime: 30_000,
  })
}

export function useCvProfileQuery(id: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => cvQueryKeys.profile(toValue(id))),
    queryFn: () => getCvProfile(toValue(id)),
    enabled: () => {
      const value = toValue(id)
      return !!value && value !== 'new'
    },
    staleTime: 15_000,
  })
}

export function useCvVersionsQuery(profileId: MaybeRef<string>, page: MaybeRef<number> = 1) {
  return useQuery({
    queryKey: computed(() => cvQueryKeys.versionsPage(toValue(page), toValue(profileId))),
    queryFn: () => getCvVersionsPage(toValue(page), 20, toValue(profileId)),
    enabled: () => !!toValue(profileId) && toValue(profileId) !== 'new',
    staleTime: 10 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  })
}

export function useAllCvVersionsQuery(enabled: MaybeRef<boolean> = true) {
  return useQuery({
    queryKey: cvQueryKeys.versionsPage(1),
    queryFn: () => getCvVersionsPage(),
    enabled: () => toValue(enabled),
    staleTime: 10 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  })
}

export function useCvVersionsPageQuery(
  page: MaybeRef<number>,
  profileId?: MaybeRef<string>,
  enabled: MaybeRef<boolean> = true,
) {
  return useQuery({
    queryKey: computed(() =>
      cvQueryKeys.versionsPage(toValue(page), profileId ? toValue(profileId) : undefined),
    ),
    queryFn: () => getCvVersionsPage(toValue(page), 20, profileId ? toValue(profileId) : undefined),
    enabled: () => toValue(enabled),
    staleTime: 10 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  })
}

export function useCvPreviewQuery(
  versionId: MaybeRef<string>,
  templateId: MaybeRef<string>,
  templateVersion: MaybeRef<string>,
) {
  return useQuery({
    queryKey: computed(() =>
      cvQueryKeys.preview(toValue(versionId), toValue(templateId), toValue(templateVersion)),
    ),
    queryFn: () => getCvPreview(toValue(versionId), toValue(templateId), toValue(templateVersion)),
    enabled: computed(
      () => Boolean(toValue(versionId)) && Boolean(toValue(templateId) && toValue(templateVersion)),
    ),
    staleTime: 10 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  })
}
