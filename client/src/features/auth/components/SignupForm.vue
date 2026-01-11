<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'
import { signupSchema } from '../schemas/signup.schema'

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

<template>
  <form @submit="onSubmit" class="space-y-4">
    <TextField name="name" label="Name" placeholder="Your name" />

    <TextField name="email" label="Email" type="email" placeholder="you@example.com" />

    <TextField name="password" label="Password" type="password" />

    <TextField name="password_confirmation" label="Confirm Password" type="password" />

    <Button type="submit" class="w-full mt-4 h-10 rounded-2xl"> Create account </Button>
  </form>
</template>
