import { defineStore } from 'pinia'
import { ref } from 'vue'
import { employeeService } from '@/features/workspace/employee/services/employee.service'
import { parseApiError, type ParsedError } from '@/utils/handleApiError'
import type { Role, InviteResult } from '@/features/workspace/employee/types/employee.types'

export const useEmployeeStore = defineStore('employeeStore', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const roles = ref<Role[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // ── Helpers ────────────────────────────────────────────────────────────────
  const clearError = () => {
    error.value = null
  }

  const setError = (parsedError: ParsedError) => {
    error.value = parsedError.message
  }

  const withLoading = async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    isLoading.value = true
    clearError()
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

  // ── Actions ────────────────────────────────────────────────────────────────
  const fetchRoles = async () => {
    if (roles.value.length > 0) return // already loaded

    return withLoading(async () => {
      roles.value = await employeeService.getRoles()
    })
  }

  const sendInvitations = async (emails: string[], role: string): Promise<InviteResult> => {
    return withLoading(async () => {
      return await employeeService.sendInvitations(emails, role)
    })
  }

  return {
    // State
    roles,
    isLoading,
    error,

    // Actions
    fetchRoles,
    sendInvitations,
    clearError,
  }
})
