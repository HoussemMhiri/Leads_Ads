<template>
  <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12">
    <!-- Back Button -->
    <RouterLink
      :to="{ name: 'signin' }"
      class="inline-flex items-center text-sm text-muted-foreground hover:text-primary mb-4"
    >
      <LeftChevronIcon />
      Back to login
    </RouterLink>

    <!-- Title -->
    <div class="text-center mb-6 sm:mb-8">
      <h1 class="text-3xl font-bold text-black">Reset Password</h1>
      <p class="mt-2 text-gray-600">Enter your new password below</p>
    </div>

    <!-- Form -->
    <ResetPasswordForm />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import ResetPasswordForm from '@/features/auth/components/ResetPasswordForm.vue'
import { useAuthStore } from '@/features/auth/store/auth.store'
import LeftChevronIcon from '@/assets/icons/navigations/LeftChevronIcon.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

onMounted(() => {
  // Clear any previous messages
  authStore.clearMessages()

  // Validate that token and email are present
  if (!route.query.token || !route.query.email) {
    console.error('Missing token or email in URL')
    // Optionally redirect to forgot password page
    router.push({ name: 'forgotPassword' })
  }
})
</script>
