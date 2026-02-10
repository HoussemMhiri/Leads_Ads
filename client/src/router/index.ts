import AuthLayout from '@/layouts/AuthLayout.vue'
import ForgetPasswordView from '@/views/ForgetPasswordView.vue'
import SigninView from '@/views/SigninView.vue'
import SignupView from '@/views/SignupView.vue'
import DashboardView from '@/views/DashboardView.vue'
import ResetPasswordView from '@/views/ResetPasswordView.vue'

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import GoogleCallbackView from '@/views/GoogleCallbackView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true },
    },
    {
      path: '/auth',
      component: AuthLayout,
      meta: { requiresGuest: true },
      children: [
        {
          path: 'sign-up',
          name: 'signup',
          component: SignupView,
        },
        {
          path: 'sign-in',
          name: 'signin',
          component: SigninView,
        },
        {
          path: 'forgot-password',
          name: 'forgotPassword',
          component: ForgetPasswordView,
        },
        {
          path: 'reset-password',
          name: 'resetPassword',
          component: ResetPasswordView,
        },
        {
          path: 'verify-email',
          name: 'verifyEmail',
          component: () => import('@/views/VerifyEmailView.vue'),
        },
      ],
    },
    {
      path: '/auth/google/callback',
      name: 'googleCallback',
      component: GoogleCallbackView,
    },
  ],
})

// Track if auth has been initialized
let authInitialized = false

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Wait for auth initialization on first navigation
  if (!authInitialized) {
    await authStore.initializeAuth()
    authInitialized = true
  }

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const requiresGuest = to.matched.some((record) => record.meta.requiresGuest)

  if (requiresAuth && !authStore.isAuthenticated) {
    // Not authenticated, redirect to login
    next({
      name: 'signin',
      query: { redirect: to.fullPath },
    })
  } else if (requiresGuest && authStore.isAuthenticated) {
    // Already logged in, redirect to dashboard
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
