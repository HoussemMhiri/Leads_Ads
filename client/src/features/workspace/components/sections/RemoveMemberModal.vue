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
        <Button variant="outline" :disabled="isPending" @click="emit('update:open', false)">
          No, cancel
        </Button>
        <Button variant="destructive" :disabled="isPending" @click="handleRemove">
          {{ isPending ? 'Removing…' : 'Yes, remove' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRemoveMember } from '@/features/workspace/employee/composables/useEmployeeMutations'
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

const props = defineProps<{ open: boolean; member: TeamMember | null }>()
const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const { mutate: removeMember, isPending } = useRemoveMember()

const errorMsg = ref<string | null>(null)

watch(
  () => props.open,
  (open) => {
    if (open) errorMsg.value = null
  },
)

const handleRemove = () => {
  if (!props.member) return

  removeMember(props.member.id, {
    onSuccess: () => emit('update:open', false),
    onError: (err) => {
      errorMsg.value = parseApiError(err).message
    },
  })
}
</script>
