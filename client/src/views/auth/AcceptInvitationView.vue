<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow p-6 sm:p-8">
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-600">Validating invitation...</p>
      </div>

      <!-- Invalid/expired link -->
      <div v-else-if="pageError" class="text-center py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Invalid Invitation</h1>
        <AlertMessage :message="pageError" type="error" />
        <RouterLink
          :to="{ name: 'signin' }"
          class="inline-block mt-6 text-sm text-blue-600 hover:underline"
        >
          Go to login
        </RouterLink>
      </div>

      <!-- Accept form -->
      <div v-else>
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Accept Invitation</h1>
          <p class="mt-2 text-gray-600">Set up your account to join the team</p>
        </div>

        <form @submit="onSubmit" class="space-y-4">
          <!-- Email (display only) -->
          <div>
            <label class="text-sm font-medium text-gray-700">Email</label>
            <p class="mt-1 px-4 py-2 bg-gray-100 rounded-lg text-gray-900">{{ inviteeEmail }}</p>
          </div>

          <TextField
            name="name"
            label="Full Name"
            placeholder="Enter your full name"
            :disabled="submitting || !!successMessage"
          />

          <TextField
            name="password"
            label="Password"
            type="password"
            placeholder="Choose a password"
            :disabled="submitting || !!successMessage"
          />

          <TextField
            name="password_confirmation"
            label="Confirm Password"
            type="password"
            placeholder="Confirm your password"
            :disabled="submitting || !!successMessage"
          />

          <AlertMessage :message="submitError" type="error" />

          <div v-if="successMessage" class="p-3 rounded-lg bg-green-50 border border-green-200">
            <p class="text-sm text-green-800">{{ successMessage }}</p>
            <p class="text-sm text-green-700 mt-1">Redirecting to login...</p>
          </div>

          <Button
            type="submit"
            class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
            :disabled="submitting || !!successMessage"
            :loading="submitting"
          >
            {{ submitting ? 'Setting up...' : 'Accept & Create Account' }}
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'

import TextField from '@/components/forms/TextField.vue'
import Button from '@/components/ui/button/Button.vue'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { employeeService } from '@/features/workspace/employee/services/employee.service'
import { acceptInvitationSchema } from '@/features/workspace/employee/schemas/employee.schema'
import { handleApiError } from '@/utils/handleApiError'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const pageError = ref('')
const submitting = ref(false)
const submitError = ref('')
const successMessage = ref('')

const tenant = route.query.tenant as string
const employee = route.query.employee as string
const expires = route.query.expires as string
const signature = route.query.signature as string

const inviteeEmail = ref('')
const workspaceSubdomain = ref<string | null>(null)

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(acceptInvitationSchema),
  initialValues: {
    name: '',
    password: '',
    password_confirmation: '',
  },
})

onMounted(async () => {
  if (!tenant || !employee || !expires || !signature) {
    pageError.value = 'Invalid invitation link. Missing required parameters.'
    loading.value = false
    return
  }

  try {
    const details = await employeeService.getInvitationDetails(tenant, employee, expires, signature)
    inviteeEmail.value = details.email
    workspaceSubdomain.value = details.workspace
  } catch (error) {
    pageError.value = handleApiError(error)
  } finally {
    loading.value = false
  }
})

const onSubmit = handleSubmit(async (values) => {
  submitting.value = true
  submitError.value = ''

  try {
    const result = await employeeService.acceptInvitation(tenant, employee, expires, signature, {
      name: values.name,
      password: values.password,
      password_confirmation: values.password_confirmation,
    })

    successMessage.value = result.message

    setTimeout(() => {
      // Redirect to employee sign-in with the workspace slug as a query param
      // so the login form is pre-filled and the X-Tenant header can be sent.
      if (workspaceSubdomain.value) {
        router.push({ name: 'employeeSignin', query: { workspace: workspaceSubdomain.value } })
      }
    }, 2000)
  } catch (error) {
    submitError.value = handleApiError(error)
  } finally {
    submitting.value = false
  }
})
</script>
