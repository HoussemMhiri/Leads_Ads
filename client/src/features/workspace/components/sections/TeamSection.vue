<template>
  <div class="space-y-6">
    <!-- Invite employees -->
    <div class="space-y-4">
      <div>
        <h4 class="text-sm font-medium">Invite Team Members</h4>
        <p class="text-xs text-muted-foreground mt-0.5">
          Send email invitations to add members to your workspace.
        </p>
      </div>

      <AlertMessage :message="inviteSuccess" type="success" />
      <AlertMessage :message="inviteError" type="error" />

      <!-- Email tag input -->
      <div class="space-y-1">
        <div
          class="flex flex-wrap items-center gap-2 px-3 py-2 rounded-md border bg-background focus-within:ring-2 focus-within:ring-ring min-h-10"
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
            type="email"
            placeholder="Type an email and press Enter"
            class="flex-1 min-w-32 py-0.5 text-sm outline-none border-none bg-transparent"
            @keydown.enter.prevent="addEmail"
            @keydown.tab.prevent="addEmail"
            @blur="addEmail"
          />
        </div>
        <p class="text-xs text-muted-foreground">
          {{ inviteEmails.length }} email{{ inviteEmails.length !== 1 ? 's' : '' }} added
        </p>
      </div>

      <!-- Role select + Send button -->
      <div class="flex items-center justify-between">
        <Select v-model="selectedRole" :disabled="rolesLoading">
          <SelectTrigger class="w-36 capitalize">
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
        <button
          type="button"
          :disabled="inviteEmails.length === 0 || rolesLoading"
          @click="handleSendInvitations"
          class="px-4 py-2 rounded-md bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ rolesLoading ? 'Sending…' : `Send Invitation${inviteEmails.length > 1 ? 's' : ''}` }}
        </button>
      </div>
    </div>
  </div>

</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useEmployeeStore } from '@/features/workspace/employee/store/employee.store'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const employeeStore = useEmployeeStore()
const { roles, isLoading: rolesLoading } = storeToRefs(employeeStore)

// ── Roles ─────────────────────────────────────────────────────────────────────
const selectedRole = ref('member')

onMounted(async () => {
  await employeeStore.fetchRoles()
  if (roles.value.length > 0) {
    selectedRole.value =
      roles.value.find((r) => r.name === 'member')?.name ?? roles.value[0]?.name ?? 'member'
  }
})

// ── Invite form ───────────────────────────────────────────────────────────────
const inviteEmails = ref<string[]>([])
const emailInput = ref('')
const inviteSuccess = ref('')

const inviteError = computed(() => employeeStore.error)

const addEmail = () => {
  const email = emailInput.value.trim().replace(/,$/, '')
  if (email && !inviteEmails.value.includes(email)) {
    inviteEmails.value.push(email)
  }
  emailInput.value = ''
}

const removeEmail = (index: number) => {
  inviteEmails.value.splice(index, 1)
}

const handleSendInvitations = async () => {
  inviteSuccess.value = ''
  employeeStore.clearError()

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
  } catch {
    // error already set in store
  }
}
</script>
