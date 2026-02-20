import api from '@/plugins/api'
import type {
  Role,
  InviteResult,
  InvitationDetails,
  AcceptInvitationData,
  AcceptInvitationResponse,
} from '@/features/workspace/employee/types/employee.types'

export const employeeService = {
  async getRoles(): Promise<Role[]> {
    const res = await api.get<Role[]>('/roles', { prefix: 'employees' })
    return res.data
  },

  async sendInvitations(emails: string[], role: string = 'member'): Promise<InviteResult> {
    const res = await api.post<InviteResult>('/invite', { emails, role }, { prefix: 'employees' })
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
