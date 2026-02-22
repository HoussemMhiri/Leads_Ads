<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12 rounded-lg shadow">
      <div class="text-center mb-6 sm:mb-8">
        <h1 class="text-3xl font-bold text-black">Welcome back</h1>
        <p class="mt-2 text-gray-600">Sign in to your workspace</p>
      </div>

      <form @submit="onSubmit" class="space-y-4">
        <TextField
          name="workspace"
          label="Workspace"
          placeholder="your-company"
          :disabled="isLoading"
        />

        <TextField
          name="email"
          label="Email"
          type="email"
          placeholder="you@example.com"
          :disabled="isLoading"
        />

        <TextField
          name="password"
          label="Password"
          type="password"
          placeholder="Your password"
          :disabled="isLoading"
        />

        <AlertMessage :message="error" type="error" />

        <Button
          type="submit"
          class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
          :disabled="isLoading || hasErrors"
          :loading="isLoading"
        >
          {{ isLoading ? 'Signing in...' : 'Sign in' }}
        </Button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-500">
        Are you a workspace owner?
        <RouterLink :to="{ name: 'signin' }" class="text-blue-600 hover:underline">
          Sign in here
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { storeToRefs } from 'pinia'

import TextField from '@/components/forms/TextField.vue'
import Button from '@/components/ui/button/Button.vue'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'

const route = useRoute()
const router = useRouter()
const employeeAuthStore = useEmployeeAuthStore()

const { isLoading, error } = storeToRefs(employeeAuthStore)

// Pre-fill workspace from ?workspace= query param (set by AcceptInvitationView)
// or from localStorage (returning employee).
const savedWorkspace =
  (route.query.workspace as string) || localStorage.getItem('employee_workspace_name') || ''

const loginSchema = z.object({
  workspace: z.string().min(1, 'Workspace is required'),
  email: z.string().email('Please enter a valid email'),
  password: z.string().min(1, 'Password is required'),
})

const { handleSubmit, errors, setFieldValue } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: { workspace: savedWorkspace, email: '', password: '' },
})

const hasErrors = computed(() => Object.keys(errors.value).length > 0)

const onSubmit = handleSubmit(async (values) => {
  // Persist workspace so the X-Tenant header is sent with the login request
  localStorage.setItem('employee_workspace_name', values.workspace)

  await employeeAuthStore.login({
    email: values.email,
    password: values.password,
  })

  router.push({ name: 'dashboard' })
})

onMounted(() => {
  employeeAuthStore.clearError()
  const ws = (route.query.workspace as string) || localStorage.getItem('employee_workspace_name') || ''
  if (ws) {
    setFieldValue('workspace', ws)
  }
})
</script>
