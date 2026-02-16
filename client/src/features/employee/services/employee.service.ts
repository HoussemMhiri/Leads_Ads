import api from '@/plugins/api'

export interface InviteResponse {
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

export const employeeService = {
  async sendInvitations(emails: string[], role: string = 'member'): Promise<InviteResponse> {
    const res = await api.post<InviteResponse>('/invite', { emails, role }, { prefix: 'employees' })
    return res.data
  },

  async getInvitationDetails(
    tenant: string,
    employee: string,
    expires: string,
    signature: string,
  ): Promise<InvitationDetails> {
    const res = await api.get<InvitationDetails>(
      `/invitation/${tenant}/${employee}/accept`,
      {
        prefix: 'employees',
        params: { expires, signature },
      },
    )
    return res.data
  },

  async acceptInvitation(
    tenant: string,
    employee: string,
    expires: string,
    signature: string,
    data: AcceptInvitationData,
  ): Promise<AcceptInvitationResponse> {
    const res = await api.post<AcceptInvitationResponse>(
      `/invitation/${tenant}/${employee}/accept`,
      data,
      {
        prefix: 'employees',
        params: { expires, signature },
      },
    )
    return res.data
  },
}
