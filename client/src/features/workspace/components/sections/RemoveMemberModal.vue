<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-sm">
      <DialogHeader>
        <DialogTitle>Remove Member</DialogTitle>
        <DialogDescription>
          Are you sure you want to remove
          <span class="font-medium text-foreground">{{ member?.name }}</span>
          from the workspace?
        </DialogDescription>
      </DialogHeader>

      <AlertMessage :message="errorMsg" type="error" />

      <DialogFooter>
        <Button variant="outline" :disabled="isRemoving" @click="emit('update:open', false)">
          No, cancel
        </Button>
        <Button variant="destructive" :disabled="isRemoving" @click="handleRemove">
          {{ isRemoving ? 'Removing…' : 'Yes, remove' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
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

const props = defineProps<{ open: boolean; member: TeamMember | null }>()
const emit = defineEmits<{
  'update:open': [value: boolean]
  removed: []
}>()

const employeeStore = useEmployeeStore()

const isRemoving = ref(false)
const errorMsg = ref<string | null>(null)

watch(
  () => props.open,
  (open) => {
    if (open) errorMsg.value = null
  },
)

const handleRemove = async () => {
  if (!props.member) return

  isRemoving.value = true
  errorMsg.value = null

  try {
    await employeeStore.removeMember(props.member.id)
    emit('removed')
    emit('update:open', false)
  } catch {
    errorMsg.value = employeeStore.error
  } finally {
    isRemoving.value = false
  }
}
</script>
