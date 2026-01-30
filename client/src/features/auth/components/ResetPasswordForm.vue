<template>
  <div>
    <form @submit="onSubmit" class="space-y-4">
      <!-- Hidden email field (pre-filled from URL) -->
      <TextField
        name="email"
        label="Email"
        type="email"
        placeholder="you@example.com"
        :disabled="true"
      />

      <TextField
        name="password"
        label="New Password"
        type="password"
        placeholder="Enter new password"
        :disabled="isLoading"
      />

      <TextField
        name="password_confirmation"
        label="Confirm New Password"
        type="password"
        placeholder="Confirm new password"
        :disabled="isLoading"
      />

      <!-- Error message -->
      <AlertMessage :message="error" type="error" />

      <!-- Success message -->
      <div v-if="successMessage" class="p-3 rounded-lg bg-green-50 border border-green-200">
        <p class="text-sm text-green-800">{{ successMessage }}</p>
        <p class="text-sm text-green-700 mt-1">Redirecting to login...</p>
      </div>

      <Button
        type="submit"
        class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
        :disabled="isLoading || !!successMessage"
        :loading="isLoading"
      >
        {{ isLoading ? 'Resetting...' : 'Reset Password' }}
      </Button>

      <p class="text-muted-foreground text-center">
        Remember your password?
        <RouterLink
          :to="{ name: 'signin' }"
          class="text-primary font-medium hover:underline cursor-pointer"
        >
          Log in
        </RouterLink>
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { watch } from 'vue'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'

import { resetPasswordSchema } from '../schemas/auth.schema'
import { useAuthStore } from '../store/auth.store'
import AlertMessage from '@/components/shared/AlertMessage.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const { isLoading, error, successMessage } = storeToRefs(authStore)

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(resetPasswordSchema),
  initialValues: {
    token: (route.query.token as string) || '',
    email: (route.query.email as string) || '',
    password: '',
    password_confirmation: '',
  },
})

// Watch for success and redirect
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      router.push({ name: 'signin' })
    }, 2000)
  }
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.resetPassword(values)
  } catch (error) {
    console.error('Failed to reset password:', error)
  }
})
</script>
