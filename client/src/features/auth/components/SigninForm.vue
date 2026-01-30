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

      <TextField name="password" label="Password" type="password" :disabled="isLoading" />

      <p class="text-primary text-sm font-medium hover:underline cursor-pointer">
        <RouterLink
          :to="{ name: 'forgotPassword' }"
          class="text-primary font-medium hover:underline cursor-pointer"
        >
          Forgot password?
        </RouterLink>
      </p>

      <!-- Error message from store -->
      <AlertMessage :message="error" type="error" />

      <Button
        type="submit"
        class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
        :disabled="isLoading"
        :loading="isLoading"
      >
        {{ isLoading ? 'Logging in...' : 'Log in' }}
      </Button>

      <div class="relative flex items-center my-2">
        <div class="grow border-t border-border"></div>

        <span class="mx-4 text-sm text-muted-foreground"> or </span>

        <div class="grow border-t border-border"></div>
      </div>

      <Button
        type="button"
        class="w-full mt-4 h-10 rounded-2xl bg-border text-black hover:bg-gray-hover cursor-pointer"
        :disabled="isLoading"
      >
        <GoogleIcon class="mr-2" /> Continue with Google
      </Button>

      <p class="text-muted-foreground text-center">
        Don't have an account?
        <RouterLink
          :to="{ name: 'signup' }"
          class="text-primary font-medium hover:underline cursor-pointer"
        >
          Sign up
        </RouterLink>
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { useRouter, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import GoogleIcon from '@/assets/icons/socials/GoogleIcon.vue'
import { loginSchema } from '../schemas/auth.schema'
import { useAuthStore } from '../store/auth.store'
import AlertMessage from '@/components/shared/AlertMessage.vue'

const router = useRouter()
const authStore = useAuthStore()

const { isLoading, error } = storeToRefs(authStore)

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: '',
    password: '',
  },
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.login(values)
    router.push({ name: 'dashboard' })
  } catch (error) {
    console.error('Login failed:', error)
  }
})
</script>
