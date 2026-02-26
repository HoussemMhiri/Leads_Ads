import { useQuery } from '@tanstack/vue-query'
import { employeeService } from '@/features/workspace/employee/services/employee.service'

export function useMembers() {
  return useQuery({
    queryKey: ['members'],
    queryFn: () => employeeService.getMembers(),
  })
}
