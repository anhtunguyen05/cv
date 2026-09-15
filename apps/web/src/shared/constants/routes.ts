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

  // JD
  JD_NEW: '/jd/new',

  // Match
  MATCH_REPORT: (matchId: string | number = ':matchId') => `/match/${matchId}`,

  // Templates
  TEMPLATES: '/templates',
} as const
