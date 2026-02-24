import api from '@/plugins/api'
import type { User } from '@/features/auth/types/auth.types'
import type { AuthenticatedEmployee } from '@/features/workspace/employee/types/employee.types'

export type MeResponse =
  | { type: 'owner'; user: User }
  | { type: 'employee'; employee: AuthenticatedEmployee; tenant: { id: string; workspace: string | null } }
  | { type: null }

export const sessionService = {
  async getMe(): Promise<MeResponse> {
    const res = await api.get<MeResponse>('/api/me')
    return res.data
  },
}
