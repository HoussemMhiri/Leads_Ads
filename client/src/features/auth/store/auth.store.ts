import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authService } from '@/features/auth/services/auth.service'
import type { User, LoginCredentials, RegisterData } from '@/features/auth/types/auth.types'



export const useAuthStore = defineStore('authStore', () => {
  const authUser = ref<User | null>(null)
  const isLoading = ref(false)

  const withLoading = async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    isLoading.value = true
    try {
      return await asyncFn()
    } catch (err) {
      console.error(err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const register = async (data: RegisterData) => {
    return withLoading(async () => {
      const response = await authService.registerUser(data)
      if (response.user) {
        authUser.value = response.user
      }
      return response
    })
  }

  const login = async (credentials: LoginCredentials) => {
    return withLoading(async () => {
      const response = await authService.loginUser(credentials)
      if (response.user) {
        authUser.value = response.user
      }
      return response
    })
  }

  const logout = async () => {
    return withLoading(async () => {
      const response = await authService.logoutUser()
      authUser.value = null
      return response
    })
  }

  return {
    // State
    isLoading,
    authUser,

    // Actions
    register,
    login,
    logout,
  }
})