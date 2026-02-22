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

  // Restore both sessions on first navigation
  if (!authInitialized) {
    await Promise.all([authStore.initializeAuth(), employeeAuthStore.initializeAuth()])
    authInitialized = true
  }

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const requiresGuest = to.matched.some((record) => record.meta.requiresGuest)

  const isAuthenticated = authStore.isAuthenticated || employeeAuthStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    // If a workspace is stored the user is an employee — send to employee login.
    // Otherwise send to the owner login page.
    if (localStorage.getItem('employee_workspace_name')) {
      next({ name: 'employeeSignin' })
    } else {
      next({ name: 'signin', query: { redirect: to.fullPath } })
    }
  } else if (requiresGuest && authStore.isAuthenticated) {
    // Only redirect owners away from guest pages — employees use a different login page.
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
