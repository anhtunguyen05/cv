import { createRouter, createWebHistory } from 'vue-router'
import { getMe } from '@/features/auth/api/auth.api'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { pinia } from '@/app/providers/pinia'
import { ApiRequestError } from '@/shared/api/client'
import { ROUTES } from '@/shared/constants/routes'

// Protected routes validate the cookie-backed current account on first access.
async function requireAuth() {
  const authStore = useAuthStore(pinia)
  if (authStore.isAuthenticated) return true

  try {
    authStore.setUser(await getMe())
    return true
  } catch (error) {
    if (error instanceof ApiRequestError && [401, 419].includes(error.status)) {
      authStore.clearAuth()
      return { path: ROUTES.LOGIN }
    }
    return false
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
