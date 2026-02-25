import { defineStore } from 'pinia'
import { ref } from 'vue'
import { workspaceService } from '@/features/workspace/services/workspace.service'
import type { UpdateWorkspaceData, UpdateWorkspaceResponse, WorkspaceInfo } from '@/features/workspace/types/workspace.types'
import { parseApiError } from '@/utils/handleApiError'

export const useWorkspaceStore = defineStore('workspaceStore', () => {
  const name = ref<string | null>(null)
  const logo = ref<string | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const withLoading = async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    isLoading.value = true
    error.value = null
    try {
      return await asyncFn()
    } catch (err) {
      error.value = parseApiError(err).message
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const setWorkspace = (info: WorkspaceInfo) => {
    name.value = info.name
    logo.value = info.logo_url
  }

  const update = async (data: UpdateWorkspaceData): Promise<UpdateWorkspaceResponse> => {
    return withLoading(async () => {
      const response = await workspaceService.update(data)
      name.value = response.workspace.name
      logo.value = response.workspace.logo_url
      return response
    })
  }

  const clear = () => {
    name.value = null
    logo.value = null
    error.value = null
  }

  return { name, logo, isLoading, error, setWorkspace, update, clear }
})
