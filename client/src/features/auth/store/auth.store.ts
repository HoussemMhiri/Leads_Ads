import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { authService } from '@/features/auth/services/auth.service'
import type {
  User,
  LoginCredentials,
  RegisterData,
  ForgotPasswordData,
  ResetPasswordData,
} from '@/features/auth/types/auth.types'
import { parseApiError, type ParsedError } from '@/utils/handleApiError'

export const useAuthStore = defineStore('authStore', () => {
  const authUser = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const validationErrors = ref<Record<string, string[]>>({})
  const successMessage = ref<string | null>(null)

  // Computed
  const isAuthenticated = computed(() => authUser.value !== null)
  const hasError = computed(() => error.value !== null)
  const hasSuccessMessage = computed(() => successMessage.value !== null)

  // Clear all errors
  const clearErrors = () => {
    error.value = null
    validationErrors.value = {}
  }

  const clearMessages = () => {
    error.value = null
    validationErrors.value = {}
    successMessage.value = null
  }

  // Set error from parsed error response
  const setError = (parsedError: ParsedError) => {
    error.value = parsedError.message
    validationErrors.value = parsedError.fieldErrors
  }

  // Set success message
  const setSuccessMessage = (message: string) => {
    successMessage.value = message
    error.value = null
    validationErrors.value = {}
  }
  // Wrap async operations with loading state and error handling

  const withLoading = async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    isLoading.value = true
    clearErrors()

    try {
      return await asyncFn()
    } catch (err) {
      const parsedError = parseApiError(err)
      setError(parsedError)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Actions
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

  const sendPasswordResetLink = async (data: ForgotPasswordData) => {
    return withLoading(async () => {
      const response = await authService.sendPasswordResetLink(data)
      if (response.message) {
        setSuccessMessage(response.message)
      }
      return response
    })
  }

  const resetPassword = async (data: ResetPasswordData) => {
    return withLoading(async () => {
      const response = await authService.resetPassword(data)
      if (response.message) {
        setSuccessMessage(response.message)
      }
      return response
    })
  }

  return {
    // State
    authUser,
    isLoading,
    error,
    validationErrors,
    successMessage,

    // Computed
    isAuthenticated,
    hasError,
    hasSuccessMessage,

    // Actions
    register,
    login,
    logout,
    clearErrors,
    sendPasswordResetLink,
    clearMessages,
    resetPassword,
  }
})
