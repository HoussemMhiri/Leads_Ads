import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { employeeService } from '@/features/workspace/employee/services/employee.service'
import type {
  AuthenticatedEmployee,
  EmployeeLoginCredentials,
} from '@/features/workspace/employee/types/employee.types'
import { parseApiError } from '@/utils/handleApiError'

const WORKSPACE_KEY = 'employee_workspace_name'

export const useEmployeeAuthStore = defineStore('employeeAuthStore', () => {
  // ── State ──────────────────────────────────────────────────────────────────

  const authEmployee = ref<AuthenticatedEmployee | null>(null)

  // Persist the workspace subdomain (e.g. "acme") so the sidebar always shows
  // the correct workspace name even before the /me call resolves.
  const workspaceName = ref<string | null>(localStorage.getItem(WORKSPACE_KEY))

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

  const persistWorkspace = (workspace: string | null) => {
    workspaceName.value = workspace
    if (workspace) localStorage.setItem(WORKSPACE_KEY, workspace)
    else localStorage.removeItem(WORKSPACE_KEY)
  }

  // ── Actions ────────────────────────────────────────────────────────────────

  /**
   * Called on app boot to restore an existing employee session.
   * Only runs when a workspace slug is stored in localStorage — without it
   * the X-Tenant header cannot be sent and there is no session to restore.
   */
  const initializeAuth = async () => {
    if (!localStorage.getItem(WORKSPACE_KEY)) return

    isLoading.value = true
    try {
      const response = await employeeService.getCurrentEmployee()
      authEmployee.value = response.employee ?? null
      if (response.tenant?.workspace) {
        persistWorkspace(response.tenant.workspace)
      }
    } catch {
      authEmployee.value = null
    } finally {
      isLoading.value = false
    }
  }

  const login = async (credentials: EmployeeLoginCredentials) => {
    return withLoading(async () => {
      const response = await employeeService.loginEmployee(credentials)

      if (response.employee) {
        authEmployee.value = response.employee
      }

      if (response.tenant?.workspace) {
        persistWorkspace(response.tenant.workspace)
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
      isLoading.value = false
    }
  }

  return {
    // State
    authEmployee,
    workspaceName,
    isLoading,
    error,

    // Computed
    isAuthenticated,

    // Actions
    login,
    logout,
    initializeAuth,
    clearError,
  }
})
