<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>Invite Team Members</DialogTitle>
        <DialogDescription>
          Send email invitations to add members to your workspace.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-2">
        <AlertMessage :message="inviteSuccess" type="success" />
        <AlertMessage :message="inviteError" type="error" />

        <!-- Email tag input -->
        <div class="space-y-1">
          <div
            :class="[
              'flex flex-wrap items-center gap-2 px-3 py-2 rounded-md border bg-background focus-within:ring-2 focus-within:ring-ring min-h-10 transition-colors',
              emailError ? 'border-destructive focus-within:ring-destructive/50' : '',
            ]"
          >
            <span
              v-for="(email, index) in inviteEmails"
              :key="index"
              class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full"
            >
              {{ email }}
              <button
                type="button"
                @click="removeEmail(index)"
                class="hover:text-primary/70 font-bold leading-none"
              >
                &times;
              </button>
            </span>
            <input
              v-model="emailInput"
              type="text"
              placeholder="Type an email and press Enter"
              class="flex-1 min-w-32 py-0.5 text-sm outline-none border-none bg-transparent"
              @keydown.enter.prevent="addEmail"
              @keydown.tab.prevent="addEmail"
              @blur="addEmail"
              @input="emailError = ''"
            />
          </div>
          <p v-if="emailError" class="text-xs text-destructive">{{ emailError }}</p>
          <p v-else class="text-xs text-muted-foreground">
            {{ inviteEmails.length }}/20 email{{ inviteEmails.length !== 1 ? 's' : '' }} added
          </p>
        </div>

        <!-- Role select -->
        <Select v-model="selectedRole" :disabled="rolesLoading">
          <SelectTrigger class="w-full capitalize">
            <SelectValue placeholder="Role" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="role in roles"
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
        <Button variant="outline" @click="emit('update:open', false)">Cancel</Button>
        <Button
          :disabled="inviteEmails.length === 0 || isSending"
          @click="handleSendInvitations"
        >
          {{ isSending ? 'Sending…' : `Send Invitation${inviteEmails.length > 1 ? 's' : ''}` }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useEmployeeStore } from '@/features/workspace/employee/store/employee.store'
import { isValidEmail } from '@/utils/validators'
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

defineProps<{ open: boolean }>()
const emit = defineEmits<{
  'update:open': [value: boolean]
  invited: []
}>()

const employeeStore = useEmployeeStore()
const { roles, isLoading: rolesLoading } = storeToRefs(employeeStore)

const selectedRole = ref('member')

onMounted(async () => {
  await employeeStore.fetchRoles()
  if (roles.value.length > 0) {
    selectedRole.value =
      roles.value.find((r) => r.name === 'member')?.name ?? roles.value[0]?.name ?? 'member'
  }
})

const inviteEmails = ref<string[]>([])
const emailInput = ref('')
const emailError = ref('')
const inviteSuccess = ref('')
const isSending = ref(false)

const inviteError = ref<string | null>(null)

const addEmail = () => {
  const email = emailInput.value.trim().replace(/,$/, '')
  if (!email) return

  if (!isValidEmail(email)) {
    emailError.value = 'Please enter a valid email address.'
    return
  }
  if (inviteEmails.value.includes(email)) {
    emailError.value = 'This email has already been added.'
    return
  }
  if (inviteEmails.value.length >= 20) {
    emailError.value = 'You can invite up to 20 people at a time.'
    return
  }

  inviteEmails.value.push(email)
  emailInput.value = ''
  emailError.value = ''
}

const removeEmail = (index: number) => {
  inviteEmails.value.splice(index, 1)
}

const handleSendInvitations = async () => {
  inviteSuccess.value = ''
  inviteError.value = null
  employeeStore.clearError()
  isSending.value = true

  try {
    const result = await employeeStore.sendInvitations(inviteEmails.value, selectedRole.value)

    const messages: string[] = []
    if (result.invited.length > 0) {
      messages.push(`Invitations sent to: ${result.invited.join(', ')}`)
    }
    if (result.already_exists.length > 0) {
      messages.push(`Already registered: ${result.already_exists.join(', ')}`)
    }

    inviteSuccess.value = messages.join('. ')
    inviteEmails.value = []
    emit('invited')
  } catch (err) {
    inviteError.value = employeeStore.error
  } finally {
    isSending.value = false
  }
}
</script>
