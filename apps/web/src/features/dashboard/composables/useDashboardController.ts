import { computed, ref } from 'vue'
import { useCvProfilesQuery } from '@/features/cv'
import { useJobDescriptionsQuery } from '@/features/jd/api/jd.queries'
import { useMatchReportsQuery } from '@/features/match/api/match.queries'

export function useDashboardController() {
  const profilesQuery = useCvProfilesQuery()
  const jobDescriptionPage = ref(1)
  const jobDescriptionsQuery = useJobDescriptionsQuery(jobDescriptionPage)
  const matchReportsQuery = useMatchReportsQuery()
  const previousJdPage = () => { if (jobDescriptionPage.value > 1) jobDescriptionPage.value -= 1 }
  const nextJdPage = () => {
    const lastPage = jobDescriptionsQuery.data.value?.lastPage ?? 1
    if (jobDescriptionPage.value < lastPage) jobDescriptionPage.value += 1
  }

  return {
    cvProfiles: computed(() => profilesQuery.data.value?.items ?? []),
    cvProfileTotal: computed(() => profilesQuery.data.value?.total ?? 0),
    isLoading: profilesQuery.isLoading,
    isError: profilesQuery.isError,
    refetch: profilesQuery.refetch,
    jobDescriptionPage,
    previousJdPage,
    nextJdPage,
    jobDescriptions: computed(() => jobDescriptionsQuery.data.value?.items ?? []),
    jobDescriptionTotal: computed(() => jobDescriptionsQuery.data.value?.total ?? 0),
    jobDescriptionTotalPages: computed(
      () => jobDescriptionsQuery.data.value?.lastPage ?? 1,
    ),
    isJdLoading: jobDescriptionsQuery.isLoading,
    isJdError: jobDescriptionsQuery.isError,
    refetchJds: jobDescriptionsQuery.refetch,
    matchReports: computed(() => matchReportsQuery.data.value?.items ?? []),
    matchReportTotal: computed(() => matchReportsQuery.data.value?.total ?? 0),
    isMatchReportsLoading: matchReportsQuery.isLoading,
    isMatchReportsError: matchReportsQuery.isError,
    refetchMatchReports: matchReportsQuery.refetch,
  }
}

export type DashboardController = ReturnType<typeof useDashboardController>
