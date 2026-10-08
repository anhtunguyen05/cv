export const ROUTES = {
  // Marketing
  LANDING: '/',

  // Auth
  LOGIN: '/login',
  REGISTER: '/register',

  // App
  DASHBOARD: '/dashboard',

  // CV
  CV_EDIT: (id: string | number = ':id') => `/cv/${id}/edit`,
  CV_PREVIEW: (id: string | number = ':id') => `/cv/${id}/preview`,
  CV_VERSION_TEMPLATES: (versionId: string = ':versionId') => `/cv-versions/${versionId}/templates`,
  CV_VERSION_PREVIEW: (versionId: string = ':versionId') => `/cv-versions/${versionId}/preview`,

  // JD
  JD_NEW: '/jd/new',
  JD_DETAIL: (id: string | number = ':id') => `/jd/${id}`,

  // Match
  MATCH_REPORTS: '/match',
  MATCH_REPORT: (matchId: string | number = ':matchId') => `/match/${matchId}`,

  // Evidence
  AI_INTERVIEW: (interviewId: string | number = ':id') => `/ai/interview/${interviewId}`,
  PATCH_REVIEW: (patchId: string | number = ':patchId') => `/patches/${patchId}`,

  // Templates
  TEMPLATES: '/templates',
} as const
