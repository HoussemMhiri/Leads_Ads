<template>
  <form @submit="onSubmit" class="space-y-4">
    <TextField
      name="name"
      label="Name (Individual or Company)"
      placeholder="Your name"
      :disabled="isLoading"
    />

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
    <AlertMessage :message="error" type="error" />

    <Button
      type="submit"
      class="w-full mt-4 h-10 rounded-2xl cursor-pointer"
      :disabled="isLoading || hasErrors"
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
      @click="handleGoogleLogin"
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
import { computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { useRouter, RouterLink } from 'vue-router'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import GoogleIcon from '@/assets/icons/socials/GoogleIcon.vue'
import { signupSchema } from '../schemas/auth.schema'
import { useAuthStore } from '../store/auth.store'
import { storeToRefs } from 'pinia'
import AlertMessage from '@/components/shared/AlertMessage.vue'

const router = useRouter()
const authStore = useAuthStore()

const { isLoading, error } = storeToRefs(authStore)

const { handleSubmit, errors } = useForm({
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
    // Redirect to verify email page instead of dashboard
    router.push({ name: 'verifyEmail' })
  } catch (error) {
    console.error('Registration failed:', error)
  }
})

const hasErrors = computed(() => Object.keys(errors.value).length > 0)

const handleGoogleLogin = () => authStore.initiateGoogleAuth()

onMounted(() => {
  authStore.clearMessages()
})
</script>
