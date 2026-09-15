import { createRouter, createWebHistory } from 'vue-router'

// Auth guard — will be wired to auth store when auth feature is ready
function requireAuth(_to: unknown, _from: unknown, next: (arg?: string) => void) {
  // TODO: replace with actual auth check from auth.store.ts
  const isAuthenticated = true // optimistic for scaffold
  if (isAuthenticated) {
    next()
  } else {
    next('/login')
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ── Marketing ──────────────────────────────────
    {
      path: '/',
      name: 'landing',
      component: () => import('@/pages/marketing/LandingPage.vue'),
      meta: { layout: 'MarketingLayout' },
    },

    // ── Auth ───────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { layout: 'AuthLayout' },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/pages/auth/RegisterPage.vue'),
      meta: { layout: 'AuthLayout' },
    },

    // ── App ────────────────────────────────────────
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/pages/dashboard/DashboardPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },

    // ── CV ─────────────────────────────────────────
    {
      path: '/cv/:id/edit',
      name: 'cv-edit',
      component: () => import('@/pages/cv/CvEditorPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },
    {
      path: '/cv/:id/preview',
      name: 'cv-preview',
      component: () => import('@/pages/cv/CvPreviewPage.vue'),
      meta: { layout: 'PreviewLayout' },
      beforeEnter: requireAuth,
    },

    // ── Job Description ────────────────────────────
    {
      path: '/jd/new',
      name: 'jd-new',
      component: () => import('@/pages/jd/JdInputPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },

    // ── Match Report ───────────────────────────────
    {
      path: '/match/:matchId',
      name: 'match-report',
      component: () => import('@/pages/match/MatchReportPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },

    // ── Templates ──────────────────────────────────
    {
      path: '/templates',
      name: 'templates',
      component: () => import('@/pages/templates/TemplatePickerPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },

    // ── Post-MVP placeholders ──────────────────────
    {
      path: '/ai/interview/:id',
      name: 'ai-interview',
      component: () => import('@/pages/ai/AiInterviewPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },
    {
      path: '/cv/:id/patches',
      name: 'cv-patches',
      component: () => import('@/pages/cv/PatchReviewPage.vue'),
      meta: { layout: 'AppLayout' },
      beforeEnter: requireAuth,
    },

    // ── Fallback ───────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

export default router
