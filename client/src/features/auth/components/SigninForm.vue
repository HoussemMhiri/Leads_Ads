<template>
  <div>
    <form @submit="onSubmit" class="space-y-4">
      <TextField name="email" label="Email" type="email" placeholder="you@example.com" />

      <TextField name="password" label="Password" type="password" />

      <p class="text-primary text-sm font-medium hover:underline cursor-pointer">
        <RouterLink
          :to="{ name: 'forgotPassword' }"
          class="text-primary font-medium hover:underline cursor-pointer"
        >
          Forgot password?
        </RouterLink>
      </p>

      <Button type="submit" class="w-full mt-4 h-10 rounded-2xl cursor-pointer"> Log in </Button>

      <div class="relative flex items-center my-2">
        <div class="grow border-t border-border"></div>

        <span class="mx-4 text-sm text-muted-foreground"> or </span>

        <div class="grow border-t border-border"></div>
      </div>

      <Button
        type="submit"
        class="w-full mt-4 h-10 rounded-2xl bg-border text-black hover:bg-gray-hover cursor-pointer"
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

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import GoogleIcon from '@/assets/icons/socials/GoogleIcon.vue'
import { RouterLink } from 'vue-router'
import { loginSchema } from '../schemas/auth.schema'

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: '',
    password: '',
  },
})

const onSubmit = handleSubmit((values) => {
  console.log(values)
})
</script>
