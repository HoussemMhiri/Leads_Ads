import axios, {  type InternalAxiosRequestConfig } from 'axios'

const BASE_URL = import.meta.env.VITE_BACKEND_BASE_URL || ''

export const ENDPOINT_PREFIXES = {
  auth: '/api/auth',
  connections: '/api/meta/connections',
  campaigns: '/api/meta/campaigns',
} as const

// Extend Axios config to support `prefix`
declare module 'axios' {
  export interface AxiosRequestConfig {
    prefix?: keyof typeof ENDPOINT_PREFIXES
  }
}

const api = axios.create({
  baseURL: BASE_URL,
  withCredentials: true, 
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
  (error) => {
    if (error.response?.status === 401) {
      /**
       * Session expired / unauthenticated
       * Later:
       * - reset auth store
       * - redirect to login
       */
      console.warn('Session expired - redirecting to login')
      // window.location.href = '/login' // Uncomment when ready
    }

    return Promise.reject(error)
  },
)

export default api