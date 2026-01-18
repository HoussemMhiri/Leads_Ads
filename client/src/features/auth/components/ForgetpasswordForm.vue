<template>
  <div>
    <form @submit="onSubmit" class="space-y-4">
      <TextField name="email" label="Email" type="email" placeholder="you@example.com" />

      <Button type="submit" class="w-full mt-4 h-10 rounded-2xl cursor-pointer"> Send </Button>

      <p class="text-muted-foreground text-center">
        Back to
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

import Button from '@/components/ui/button/Button.vue'
import TextField from '@/components/forms/TextField.vue'

import { RouterLink } from 'vue-router'
import { loginSchema } from '../schemas/auth.schema'

const { handleSubmit } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: '',
  },
})

const onSubmit = handleSubmit((values) => {
  console.log(values)
})
</script>
