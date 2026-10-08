import { useQueryClient } from '@tanstack/vue-query'
import type { MaybeRef } from 'vue'
import { dashboardQueryKeys } from '@/features/dashboard/api/dashboard.keys'
import { createCvProfile, createCvVersion, updateCvSection, updateCvTitle } from '../api/cv.api'
import { cvQueryKeys } from '../api/cv.keys'
import { useCvProfileQuery, useCvVersionsQuery } from '../api/cv.queries'
import type { CvDocument, CvProfile, CvSectionKey, PersonalInformation } from '../types/cv.types'

export function useCvEditorOperations(profileId: MaybeRef<string>, versionPage: MaybeRef<number>) {
  const queryClient = useQueryClient()
  const profileQuery = useCvProfileQuery(profileId)
  const versionsQuery = useCvVersionsQuery(profileId, versionPage)

  function cacheProfile(profile: CvProfile): void {
    queryClient.setQueryData(cvQueryKeys.profile(profile.id), profile)
  }

  return {
    profileQuery,
    versionsQuery,
    createProfile: (title: string, personalInformation: PersonalInformation) =>
      createCvProfile(title, personalInformation),
    updateTitle: (id: string, title: string, revision: number) =>
      updateCvTitle(id, title, revision),
    updateSection: (
      id: string,
      section: CvSectionKey,
      value: CvDocument[CvSectionKey],
      revision: number,
    ) => updateCvSection(id, section, value, revision),
    createVersion: (id: string, name: string, revision: number) =>
      createCvVersion(id, name, revision),
    cacheProfile,
    invalidateProfiles: () =>
      Promise.all([
        queryClient.invalidateQueries({ queryKey: cvQueryKeys.profilesList() }),
        queryClient.invalidateQueries({ queryKey: dashboardQueryKeys.summary() }),
      ]),
    invalidateVersions: () =>
      queryClient.invalidateQueries({ queryKey: cvQueryKeys.versionsRoot() }),
  }
}
