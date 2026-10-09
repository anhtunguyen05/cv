import { computed, ref } from 'vue'
import { useCvProfilesQuery } from '@/features/cv'
import { useJobDescriptionsQuery } from '@/features/jd/api/jd.queries'
import { usePageNavigation } from '@/shared/composables/usePageNavigation'
import { useDashboardSummaryQuery } from '../api/dashboard.queries'

export function useDashboardController() {
  const profilePage = ref(1)
  const profilesQuery = useCvProfilesQuery(profilePage)
  const profileNavigation = usePageNavigation({
    page: profilePage,
    lastPage: () => profilesQuery.data.value?.lastPage,
    isFetching: profilesQuery.isFetching,
  })
  const jobDescriptionPage = ref(1)
  const jobDescriptionsQuery = useJobDescriptionsQuery(jobDescriptionPage)
  const jobDescriptionNavigation = usePageNavigation({
    page: jobDescriptionPage,
    lastPage: () => jobDescriptionsQuery.data.value?.lastPage,
    isFetching: jobDescriptionsQuery.isFetching,
  })
  const summaryQuery = useDashboardSummaryQuery()

  return {
    profiles: {
      items: computed(() => profilesQuery.data.value?.items ?? []),
      total: computed(
        () => summaryQuery.data.value?.cv_profiles ?? profilesQuery.data.value?.total ?? 0,
      ),
      navigation: profileNavigation,
      isLoading: profilesQuery.isLoading,
      isError: profilesQuery.isError,
      refetch: profilesQuery.refetch,
    },
    jobDescriptions: {
      items: computed(() => jobDescriptionsQuery.data.value?.items ?? []),
      total: computed(
        () =>
          summaryQuery.data.value?.job_descriptions ?? jobDescriptionsQuery.data.value?.total ?? 0,
      ),
      navigation: jobDescriptionNavigation,
      isLoading: jobDescriptionsQuery.isLoading,
      isError: jobDescriptionsQuery.isError,
      refetch: jobDescriptionsQuery.refetch,
    },
    stats: {
      cvProfileTotal: computed(
        () => summaryQuery.data.value?.cv_profiles ?? profilesQuery.data.value?.total ?? 0,
      ),
      jobDescriptionTotal: computed(
        () =>
          summaryQuery.data.value?.job_descriptions ?? jobDescriptionsQuery.data.value?.total ?? 0,
      ),
      matchReportTotal: computed(() => summaryQuery.data.value?.match_reports ?? 0),
      isMatchReportsLoading: summaryQuery.isLoading,
      isMatchReportsError: summaryQuery.isError,
      refetchMatchReports: summaryQuery.refetch,
    },
  }
}

export type DashboardController = ReturnType<typeof useDashboardController>
export type DashboardProfilesContract = DashboardController['profiles']
export type DashboardJobDescriptionsContract = DashboardController['jobDescriptions']
export type DashboardStatsContract = DashboardController['stats']
