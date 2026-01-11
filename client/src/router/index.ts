import AuthLayout from '@/layouts/AuthLayout.vue'
import SigninView from '@/views/SigninView.vue'
import SignupView from '@/views/SignupView.vue'

import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [

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
      ],
    },
  ],
})

export default router
