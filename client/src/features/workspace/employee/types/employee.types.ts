export interface Employee {
  id: number
  name: string
  email: string
  role: string
  invited_by: number | null
  invited_at: string | null
  accepted_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface Role {
  id: number
  name: string
}

// ── Invitation ────────────────────────────────────────────────────────────────

export interface InvitePayload {
  emails: string[]
  role: string
}

export interface InviteResult {
  message: string
  invited: string[]
  already_exists: string[]
}

export interface InvitationDetails {
  email: string
  tenant: string
}

export interface AcceptInvitationData {
  name: string
  password: string
  password_confirmation: string
}

export interface AcceptInvitationResponse {
  message: string
}
