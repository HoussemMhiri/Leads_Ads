import AuthLayout from '@/layouts/AuthLayout.vue'
import ForgetPasswordView from '@/views/ForgetPasswordView.vue'
import SigninView from '@/views/SigninView.vue'
import SignupView from '@/views/SignupView.vue'
import DashboardView from '@/views/DashboardView.vue'

import { createRouter, createWebHistory } from 'vue-router'
import ResetPasswordView from '@/views/ResetPasswordView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView,
    },
    {
      path: '/auth',
      component: AuthLayout,
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
      ],
    },
  ],
})

export default router
