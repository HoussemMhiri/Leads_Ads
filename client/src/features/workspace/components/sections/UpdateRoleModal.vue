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

        <Select v-model="selectedRole" :disabled="isUpdating">
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
        <Button variant="outline" :disabled="isUpdating" @click="emit('update:open', false)">
          Cancel
        </Button>
        <Button :disabled="isUpdating || !selectedRole" @click="handleUpdate">
          {{ isUpdating ? 'Updating…' : 'Update role' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useEmployeeStore } from '@/features/workspace/employee/store/employee.store'
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
  updated: []
}>()

const employeeStore = useEmployeeStore()
const { roles } = storeToRefs(employeeStore)

const selectedRole = ref('')
const isUpdating = ref(false)
const errorMsg = ref<string | null>(null)

const availableRoles = computed(() =>
  roles.value.filter((r) => r.name !== props.member?.role),
)

watch(
  () => props.open,
  (open) => {
    if (open) {
      selectedRole.value = ''
      errorMsg.value = null
      employeeStore.fetchRoles()
    }
  },
)

const handleUpdate = async () => {
  if (!props.member || !selectedRole.value) return

  isUpdating.value = true
  errorMsg.value = null

  try {
    await employeeStore.updateMemberRole(props.member.id, selectedRole.value)
    emit('updated')
    emit('update:open', false)
  } catch {
    errorMsg.value = employeeStore.error
  } finally {
    isUpdating.value = false
  }
}
</script>
