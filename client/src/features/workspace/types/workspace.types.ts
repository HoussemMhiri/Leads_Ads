export interface WorkspaceInfo {
  name: string | null
  logo_url: string | null
}

export interface UpdateWorkspaceData {
  name?: string
  logo?: File
}

export interface UpdateWorkspaceResponse {
  message: string
  workspace: WorkspaceInfo
}
