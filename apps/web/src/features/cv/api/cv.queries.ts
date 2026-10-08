import { useQuery } from '@tanstack/vue-query'
import {
  getCvPreview,
  getCvProfiles,
  getCvProfile,
  getCvVersions,
  getCvVersionsPage,
} from './cv.api'
import { cvQueryKeys } from './cv.keys'
import { computed, toValue, type MaybeRef } from 'vue'

export function useCvProfilesQuery() {
  return useQuery({
    queryKey: cvQueryKeys.profiles(),
    queryFn: getCvProfiles,
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
  })
}

export function useCvVersionsQuery(profileId: MaybeRef<string>) {
  return useQuery({
    queryKey: computed(() => cvQueryKeys.versions(toValue(profileId))),
    queryFn: () => getCvVersions(toValue(profileId)),
    enabled: () => !!toValue(profileId) && toValue(profileId) !== 'new',
  })
}

export function useAllCvVersionsQuery(enabled: MaybeRef<boolean> = true) {
  return useQuery({
    queryKey: cvQueryKeys.versions(),
    queryFn: () => getCvVersions(),
    enabled: () => toValue(enabled),
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
    queryFn: () =>
      getCvPreview(toValue(versionId), toValue(templateId), toValue(templateVersion)),
    enabled: computed(
      () => Boolean(toValue(versionId)) && Boolean(toValue(templateId) && toValue(templateVersion)),
    ),
  })
}
