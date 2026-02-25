<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12 rounded-lg shadow">
      <div class="text-center mb-6 sm:mb-8">
        <h1 class="text-3xl font-bold text-black">Welcome back</h1>
        <p class="mt-2 text-gray-600">Sign in to your workspace</p>
      </div>

      <form @submit="onSubmit" class="space-y-4">
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
import { computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { storeToRefs } from 'pinia'

import TextField from '@/components/forms/TextField.vue'
import Button from '@/components/ui/button/Button.vue'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { employeeLoginSchema } from '@/features/workspace/employee/schemas/employee.schema'

const router = useRouter()
const employeeAuthStore = useEmployeeAuthStore()

const { isLoading, error } = storeToRefs(employeeAuthStore)

const { handleSubmit, errors } = useForm({
  validationSchema: toTypedSchema(employeeLoginSchema),
})

const hasErrors = computed(() => Object.keys(errors.value).length > 0)

const onSubmit = handleSubmit(async (values) => {
  await employeeAuthStore.login(values)
  router.push({ name: 'dashboard' })
})
</script>
