import api from '@/plugins/api'
import type {
  Role,
  InviteResult,
  InvitationDetails,
  AcceptInvitationData,
  AcceptInvitationResponse,
  EmployeeLoginCredentials,
  EmployeeAuthResponse,
} from '@/features/workspace/employee/types/employee.types'

export const employeeService = {
  // ── Invitation management ──────────────────────────────────────────────────

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

  // ── Employee auth ──────────────────────────────────────────────────────────

  async loginEmployee(data: EmployeeLoginCredentials): Promise<EmployeeAuthResponse> {
    const res = await api.post<EmployeeAuthResponse>('/login', data, { prefix: 'employees' })
    return res.data
  },

  async logoutEmployee(): Promise<{ message: string }> {
    const res = await api.post<{ message: string }>('/logout', {}, { prefix: 'employees' })
    return res.data
  },

  async getCurrentEmployee(): Promise<EmployeeAuthResponse> {
    const res = await api.get<EmployeeAuthResponse>('/me', {
      prefix: 'employees',
      skipAuthRedirect: true,
    })
    return res.data
  },
}
