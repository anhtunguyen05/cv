import { useQuery } from '@tanstack/vue-query'
import { getCvProfiles, getCvProfile, getCvVersions } from './cv.api'
import type { MaybeRef } from 'vue'
import { toValue } from 'vue'

export function useCvProfilesQuery() {
  return useQuery({
    queryKey: ['cv-profiles'],
    queryFn: getCvProfiles,
  })
}

export function useCvProfileQuery(id: MaybeRef<number>) {
  return useQuery({
    queryKey: ['cv-profiles', id],
    queryFn: () => getCvProfile(toValue(id)),
    enabled: () => !!toValue(id),
  })
}

export function useCvVersionsQuery(profileId: MaybeRef<number>) {
  return useQuery({
    queryKey: ['cv-versions', profileId],
    queryFn: () => getCvVersions(toValue(profileId)),
    enabled: () => !!toValue(profileId),
  })
}
