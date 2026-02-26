import { useQuery } from '@tanstack/vue-query'
import { employeeService } from '@/features/workspace/employee/services/employee.service'

export function useRoles() {
  return useQuery({
    queryKey: ['roles'],
    queryFn: () => employeeService.getRoles(),
    staleTime: 1000 * 60 * 60, // 1 hour — roles rarely change
  })
}
