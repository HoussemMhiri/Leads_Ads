import api from '@/plugins/api'
import type { UpdateWorkspaceData, UpdateWorkspaceResponse } from '@/features/workspace/types/workspace.types'

export const workspaceService = {
  async update(data: UpdateWorkspaceData): Promise<UpdateWorkspaceResponse> {
    const form = new FormData()
    if (data.name !== undefined) form.append('name', data.name)
    if (data.logo !== undefined) form.append('logo', data.logo)
    const res = await api.post<UpdateWorkspaceResponse>('/', form, {
      prefix: 'workspace',
    })
    return res.data
  },
}
