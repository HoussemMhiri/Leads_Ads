import AuthLayout from '@/layouts/AuthLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import ForgetPasswordView from '@/views/auth/ForgetPasswordView.vue'
import SigninView from '@/views/auth/SigninView.vue'
import SignupView from '@/views/auth/SignupView.vue'
import DashboardView from '@/views/DashboardView.vue'
import ResetPasswordView from '@/views/auth/ResetPasswordView.vue'

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { useWorkspaceStore } from '@/features/workspace/store/workspace.store'
import GoogleCallbackView from '@/views/auth/GoogleCallbackView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: MainLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: DashboardView,
        },
      ],
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
          component: () => import('@/views/auth/VerifyEmailView.vue'),
        },
      ],
    },
    {
      path: '/auth/google/callback',
      name: 'googleCallback',
      component: GoogleCallbackView,
    },
    {
      path: '/invitation/accept',
      name: 'acceptInvitation',
      component: () => import('@/views/auth/AcceptInvitationView.vue'),
    },
    // Employee login — lives outside /auth so the `requiresGuest` guard doesn't
    // block owners who are already logged in as users.
    {
      path: '/employee/sign-in',
      name: 'employeeSignin',
      component: () => import('@/views/auth/EmployeeSigninView.vue'),
    },
  ],
})

// Track if auth has been initialized
let authInitialized = false

// Navigation guard
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()
  const employeeAuthStore = useEmployeeAuthStore()
  const workspaceStore = useWorkspaceStore()

  // Restore session on first navigation — single request, never 401
  if (!authInitialized) {
    const { sessionService } = await import('@/features/auth/services/session.service')
    const me = await sessionService.getMe()
    if (me.type === 'owner') {
      authStore.authUser = me.user
      workspaceStore.setWorkspace({
        name: me.user.tenant?.company_name ?? null,
        logo_url: me.user.tenant?.logo_url ?? null,
      })
    } else if (me.type === 'employee') {
      employeeAuthStore.authEmployee = me.employee
      employeeAuthStore.workspaceName = me.tenant?.workspace ?? null
      workspaceStore.setWorkspace({
        name: me.tenant?.name ?? null,
        logo_url: me.tenant?.logo_url ?? null,
      })
    }
    authInitialized = true
  }

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const requiresGuest = to.matched.some((record) => record.meta.requiresGuest)

  const isAuthenticated = authStore.isAuthenticated || employeeAuthStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    next({ name: 'signin', query: { redirect: to.fullPath } })
  } else if (requiresGuest && authStore.isAuthenticated) {
    // Only redirect owners away from guest pages — employees use a different login page.
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
