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

export function useCvProfileQuery(id: MaybeRef<string>) {
  return useQuery({
    queryKey: ['cv-profiles', id],
    queryFn: () => getCvProfile(toValue(id)),
    enabled: () => {
      const value = toValue(id)
      return !!value && value !== 'new'
    },
  })
}

export function useCvVersionsQuery(profileId: MaybeRef<string>) {
  return useQuery({
    queryKey: ['cv-versions', profileId],
    queryFn: () => getCvVersions(toValue(profileId)),
    enabled: () => !!toValue(profileId) && toValue(profileId) !== 'new',
  })
}
