<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-sm">
      <DialogHeader>
        <DialogTitle>Update Role</DialogTitle>
        <DialogDescription>
          Change the role for <span class="font-medium text-foreground">{{ member?.name }}</span>.
        </DialogDescription>
      </DialogHeader>

      <div class="py-2">
        <AlertMessage :message="errorMsg" type="error" />

        <Select v-model="selectedRole" :disabled="isPending">
          <SelectTrigger class="w-full capitalize">
            <SelectValue placeholder="Select a role" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="role in availableRoles"
              :key="role.id"
              :value="role.name"
              class="capitalize"
            >
              {{ role.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <DialogFooter>
        <Button variant="outline" :disabled="isPending" @click="emit('update:open', false)">
          Cancel
        </Button>
        <Button :disabled="isPending || !selectedRole" @click="handleUpdate">
          {{ isPending ? 'Updating…' : 'Update role' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoles } from '@/features/workspace/employee/composables/useRoles'
import { useUpdateMemberRole } from '@/features/workspace/employee/composables/useEmployeeMutations'
import { parseApiError } from '@/utils/handleApiError'
import type { TeamMember } from '@/features/workspace/employee/types/employee.types'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const props = defineProps<{ open: boolean; member: TeamMember | null }>()
const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const { data: roles } = useRoles()
const { mutate: updateRole, isPending } = useUpdateMemberRole()

const selectedRole = ref('')
const errorMsg = ref<string | null>(null)

const availableRoles = computed(() =>
  (roles.value ?? []).filter((r) => r.name !== props.member?.role),
)

watch(
  () => props.open,
  (open) => {
    if (open) {
      selectedRole.value = ''
      errorMsg.value = null
    }
  },
)

const handleUpdate = () => {
  if (!props.member || !selectedRole.value) return

  updateRole(
    { employeeId: props.member.id, role: selectedRole.value },
    {
      onSuccess: () => emit('update:open', false),
      onError: (err) => {
        errorMsg.value = parseApiError(err).message
      },
    },
  )
}
</script>
