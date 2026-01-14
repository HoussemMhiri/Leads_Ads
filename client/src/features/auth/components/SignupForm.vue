<template>
  <form @submit="onSubmit" class="space-y-4">
    <TextField name="name" label="Name" placeholder="Your name" />

    <TextField name="email" label="Email" type="email" placeholder="you@example.com" />

    <TextField name="password" label="Password" type="password" />

    <TextField name="password_confirmation" label="Confirm Password" type="password" />

    <Button type="submit" class="w-full mt-4 h-10 rounded-2xl cursor-pointer">
      Create account
    </Button>

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
  </form>
</template>

<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import { signupSchema } from '../schemas/signup.schema'
import GoogleIcon from '@/assets/icons/socials/GoogleIcon.vue'

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(signupSchema),
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
})

const onSubmit = handleSubmit((values) => {
  console.log(values)
})
</script>
