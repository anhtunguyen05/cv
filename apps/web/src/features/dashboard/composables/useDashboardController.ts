import { computed, ref } from 'vue'
import { useCvProfilesQuery } from '@/features/cv'
import { useJobDescriptionsQuery } from '@/features/jd/api/jd.queries'
import { useDashboardSummaryQuery } from '../api/dashboard.queries'

export function useDashboardController() {
  const profilePage = ref(1)
  const profilesQuery = useCvProfilesQuery(profilePage)
  const jobDescriptionPage = ref(1)
  const jobDescriptionsQuery = useJobDescriptionsQuery(jobDescriptionPage)
  const summaryQuery = useDashboardSummaryQuery()
  const previousJdPage = () => {
    if (jobDescriptionPage.value > 1) jobDescriptionPage.value -= 1
  }
  const nextJdPage = () => {
    const lastPage = jobDescriptionsQuery.data.value?.lastPage ?? 1
    if (jobDescriptionPage.value < lastPage) jobDescriptionPage.value += 1
  }
  const previousProfilePage = () => {
    if (profilePage.value > 1) profilePage.value -= 1
  }
  const nextProfilePage = () => {
    const lastPage = profilesQuery.data.value?.lastPage ?? 1
    if (profilePage.value < lastPage) profilePage.value += 1
  }

  return {
    cvProfiles: computed(() => profilesQuery.data.value?.items ?? []),
    cvProfileTotal: computed(() => summaryQuery.data.value?.cv_profiles ?? profilesQuery.data.value?.total ?? 0),
    cvProfilePage: profilePage,
    cvProfileTotalPages: computed(() => profilesQuery.data.value?.lastPage ?? 1),
    previousProfilePage,
    nextProfilePage,
    isLoading: profilesQuery.isLoading,
    isError: profilesQuery.isError,
    refetch: profilesQuery.refetch,
    jobDescriptionPage,
    previousJdPage,
    nextJdPage,
    jobDescriptions: computed(() => jobDescriptionsQuery.data.value?.items ?? []),
    jobDescriptionTotal: computed(
      () => summaryQuery.data.value?.job_descriptions ?? jobDescriptionsQuery.data.value?.total ?? 0,
    ),
    jobDescriptionTotalPages: computed(() => jobDescriptionsQuery.data.value?.lastPage ?? 1),
    isJdLoading: jobDescriptionsQuery.isLoading,
    isJdError: jobDescriptionsQuery.isError,
    refetchJds: jobDescriptionsQuery.refetch,
    matchReportTotal: computed(() => summaryQuery.data.value?.match_reports ?? 0),
    isMatchReportsLoading: summaryQuery.isLoading,
    isMatchReportsError: summaryQuery.isError,
    refetchMatchReports: summaryQuery.refetch,
    isSummaryLoading: summaryQuery.isLoading,
    isSummaryError: summaryQuery.isError,
    refetchSummary: summaryQuery.refetch,
  }
}

export type DashboardController = ReturnType<typeof useDashboardController>
