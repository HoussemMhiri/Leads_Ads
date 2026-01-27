<template>
  <form @submit="onSubmit" class="space-y-4">
    <TextField name="name" label="Name" placeholder="Your name" :disabled="isLoading" />

    <TextField
      name="email"
      label="Email"
      type="email"
      placeholder="you@example.com"
      :disabled="isLoading"
    />

    <TextField name="password" label="Password" type="password" :disabled="isLoading" />

    <TextField
      name="password_confirmation"
      label="Confirm Password"
      type="password"
      :disabled="isLoading"
    />

    <!-- Error message from store -->
    <ErrorMessage :message="error" />

    <Button
      type="submit"
      class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
      :disabled="isLoading"
      :loading="isLoading"
    >
      {{ isLoading ? 'Creating account...' : 'Create account' }}
    </Button>

    <div class="relative flex items-center my-2">
      <div class="grow border-t border-border"></div>
      <span class="mx-4 text-sm text-muted-foreground">or</span>
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
      Have an account?
      <RouterLink
        :to="{ name: 'signin' }"
        class="text-primary font-medium hover:underline cursor-pointer"
      >
        Log in
      </RouterLink>
    </p>
  </form>
</template>

<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { useRouter, RouterLink } from 'vue-router'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import GoogleIcon from '@/assets/icons/socials/GoogleIcon.vue'
import { signupSchema } from '../schemas/auth.schema'
import { useAuthStore } from '../store/auth.store'
import ErrorMessage from '@/components/shared/ErrorMessage.vue'
import { storeToRefs } from 'pinia'

const router = useRouter()
const authStore = useAuthStore()

const { isLoading, error } = storeToRefs(authStore)

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(signupSchema),
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.register(values)
    router.push({ name: 'dashboard' })
  } catch (error) {
    console.error('Registration failed:', error)
  }
})
</script>
