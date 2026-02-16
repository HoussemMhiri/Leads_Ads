<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome, {{ authUser?.name }}!</h1>
        <p class="text-gray-600 mb-6">Your account has been successfully created.</p>

        <div class="flex gap-4">
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            Logout
          </button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Invite Employee</h2>
        <p class="text-gray-600 mb-4">Send an email invitation to add a new employee to your team.</p>

        <!-- Success message -->
        <div v-if="inviteSuccess" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200">
          <p class="text-sm text-green-800">{{ inviteSuccess }}</p>
        </div>

        <!-- Error message -->
        <div v-if="inviteError" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
          <p class="text-sm text-red-800">{{ inviteError }}</p>
        </div>

        <div
          class="flex flex-wrap items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500"
        >
          <span
            v-for="(email, index) in inviteEmails"
            :key="index"
            class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
          >
            {{ email }}
            <button
              type="button"
              @click="removeEmail(index)"
              class="text-blue-600 hover:text-blue-900 font-bold leading-none"
            >
              &times;
            </button>
          </span>
          <input
            v-model="emailInput"
            type="email"
            placeholder="Type an email and press Enter"
            class="flex-1 min-w-50 py-1 outline-none border-none bg-transparent"
            @keydown.enter.prevent="addEmail"
            @keydown.,="addEmail"
          />
        </div>
        <div class="mt-3 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            {{ inviteEmails.length }} email{{ inviteEmails.length !== 1 ? 's' : '' }} added
          </p>
          <button
            :disabled="inviteEmails.length === 0 || inviteLoading"
            @click="handleSendInvitations"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ inviteLoading ? 'Sending...' : `Send Invitation${inviteEmails.length > 1 ? 's' : ''}` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { storeToRefs } from 'pinia'
import { employeeService } from '@/features/employee/services/employee.service'
import { handleApiError } from '@/utils/handleApiError'

const inviteEmails = ref<string[]>([])
const emailInput = ref('')
const inviteLoading = ref(false)
const inviteSuccess = ref('')
const inviteError = ref('')

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
  inviteLoading.value = true
  inviteSuccess.value = ''
  inviteError.value = ''

  try {
    const result = await employeeService.sendInvitations(inviteEmails.value)

    const messages: string[] = []
    if (result.invited.length > 0) {
      messages.push(`Invitations sent to: ${result.invited.join(', ')}`)
    }
    if (result.already_exists.length > 0) {
      messages.push(`Already registered: ${result.already_exists.join(', ')}`)
    }

    inviteSuccess.value = messages.join('. ')
    inviteEmails.value = []
  } catch (error) {
    inviteError.value = handleApiError(error)
  } finally {
    inviteLoading.value = false
  }
}

const router = useRouter()
const authStore = useAuthStore()
const { authUser } = storeToRefs(authStore)
const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push({ name: 'signin' })
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
