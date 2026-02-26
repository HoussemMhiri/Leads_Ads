import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { employeeService } from '@/features/workspace/employee/services/employee.service'

export function useInviteMembers() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ emails, role }: { emails: string[]; role: string }) =>
      employeeService.sendInvitations(emails, role),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['members'] })
    },
  })
}

export function useUpdateMemberRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ employeeId, role }: { employeeId: number; role: string }) =>
      employeeService.updateMemberRole(employeeId, role),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['members'] })
    },
  })
}

export function useRemoveMember() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (employeeId: number) => employeeService.removeMember(employeeId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['members'] })
    },
  })
}
