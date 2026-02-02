import router from '@/router'
import axios, { type InternalAxiosRequestConfig } from 'axios'

const BASE_URL = import.meta.env.VITE_BACKEND_BASE_URL || 'http://localhost:8000'

export const ENDPOINT_PREFIXES = {
  auth: '/api/auth',
  connections: '/api/meta/connections',
  campaigns: '/api/meta/campaigns',
} as const

// Extend Axios config to support `prefix`
declare module 'axios' {
  export interface AxiosRequestConfig {
    prefix?: keyof typeof ENDPOINT_PREFIXES
    skipAuthRedirect?: boolean
  }
}

const api = axios.create({
  baseURL: BASE_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
})

// --------------------
// Request interceptor
// --------------------
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (config.prefix) {
      const prefix = ENDPOINT_PREFIXES[config.prefix]

      // This check is redundant now due to TypeScript, but keep for runtime safety
      if (!prefix) {
        throw new Error(`Unknown API prefix: ${config.prefix}`)
      }

      config.url = `${prefix}${config.url}`
    }

    return config
  },
  (error) => Promise.reject(error),
)

// --------------------
// Response interceptor
// --------------------
api.interceptors.response.use(
  (response) => response,

  async (error) => {
    // Skip auto-redirect if flag is set
    if (error.config?.skipAuthRedirect) {
      return Promise.reject(error)
    }

    if (error.response?.status === 401) {
      const { useAuthStore } = await import('@/features/auth/store/auth.store')
      const authStore = useAuthStore()

      authStore.authUser = null
      authStore.clearErrors()

      // Redirect to login (avoid redirect loops)
      const currentRoute = router.currentRoute.value.name
      const authRoutes = ['signin', 'signup', 'forgotPassword', 'resetPassword']

      if (!authRoutes.includes(currentRoute as string)) {
        await router.push({
          name: 'signin',
          query: { redirect: router.currentRoute.value.fullPath },
        })
      }
    }

    return Promise.reject(error)
  },
)

export default api
