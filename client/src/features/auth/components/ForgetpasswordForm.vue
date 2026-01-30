<template>
  <div>
    <form @submit="onSubmit" class="space-y-4">
      <TextField
        name="email"
        label="Email"
        type="email"
        placeholder="you@example.com"
        :disabled="isLoading"
      />

      <!-- Error message -->
      <AlertMessage :message="error" type="error" />

      <!-- Success message -->
      <AlertMessage :message="successMessage" type="success" />

      <Button
        type="submit"
        class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
        :disabled="isLoading"
        :loading="isLoading"
      >
        {{ isLoading ? 'Sending...' : 'Send Reset Link' }}
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
import { RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import { forgotPasswordSchema } from '../schemas/auth.schema'
import { useAuthStore } from '../store/auth.store'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { onMounted } from 'vue'

const authStore = useAuthStore()
const { isLoading, error, successMessage } = storeToRefs(authStore)

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(forgotPasswordSchema),
  initialValues: {
    email: '',
  },
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.sendPasswordResetLink(values)
  } catch (error) {
    console.error('Failed to send reset link:', error)
  }
})

onMounted(() => {
  authStore.clearMessages()
})
</script>
