import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { employeeService } from '@/features/workspace/employee/services/employee.service'
import type {
  AuthenticatedEmployee,
  EmployeeLoginCredentials,
} from '@/features/workspace/employee/types/employee.types'
import { parseApiError } from '@/utils/handleApiError'
import { useWorkspaceStore } from '@/features/workspace/store/workspace.store'

export const useEmployeeAuthStore = defineStore('employeeAuthStore', () => {
  // ── State ──────────────────────────────────────────────────────────────────

  const authEmployee = ref<AuthenticatedEmployee | null>(null)
  const workspaceName = ref<string | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // ── Computed ───────────────────────────────────────────────────────────────

  const isAuthenticated = computed(() => authEmployee.value !== null)

  // ── Helpers ────────────────────────────────────────────────────────────────

  const clearError = () => {
    error.value = null
  }

  const withLoading = async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    isLoading.value = true
    clearError()
    try {
      return await asyncFn()
    } catch (err) {
      const parsed = parseApiError(err)
      error.value = parsed.message
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // ── Actions ────────────────────────────────────────────────────────────────

  const login = async (credentials: EmployeeLoginCredentials) => {
    return withLoading(async () => {
      const response = await employeeService.loginEmployee(credentials)
      if (response.employee) {
        authEmployee.value = response.employee
      }
      workspaceName.value = response.tenant?.workspace ?? null
      if (response.tenant) {
        useWorkspaceStore().setWorkspace({
          name: response.tenant.name,
          logo_url: response.tenant.logo_url,
        })
      }
      return response
    })
  }

  const logout = async () => {
    isLoading.value = true
    clearError()
    try {
      await employeeService.logoutEmployee()
    } catch (err) {
      const parsed = parseApiError(err)
      if (parsed.statusCode !== 401) {
        error.value = parsed.message
      }
    } finally {
      authEmployee.value = null
      workspaceName.value = null
      isLoading.value = false
    }
  }

  return {
    authEmployee,
    workspaceName,
    isLoading,
    error,
    isAuthenticated,
    login,
    logout,
    clearError,
  }
})
