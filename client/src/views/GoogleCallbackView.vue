<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
      <div v-if="isLoading" class="space-y-4">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
        <p class="text-gray-600">Completing sign in with Google...</p>
      </div>

      <div v-else-if="error" class="space-y-4">
        <div class="text-red-500 text-5xl">⚠️</div>
        <h2 class="text-2xl font-bold text-gray-900">Authentication Failed</h2>
        <p class="text-gray-600">{{ error }}</p>
        <button
          @click="goToLogin"
          class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"
        >
          Back to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isLoading = ref(true)
const error = ref<string | null>(null)

const goToLogin = () => {
  router.push({ name: 'signin' })
}

onMounted(async () => {
  const code = route.query.code as string | undefined
  const errorParam = route.query.error

  if (errorParam) {
    error.value = 'Google authentication failed. Please try again.'
    isLoading.value = false
    return
  }

  if (code) {
    try {
      // Exchange the one-time code for a session via a proxied request.
      // This sets the session cookie on the correct origin.
      await authStore.exchangeGoogleCode(code)

      const redirect = route.query.redirect as string
      router.push(redirect || { name: 'dashboard' })
    } catch (err) {
      error.value = 'Failed to authenticate. Please try again.'
      isLoading.value = false
    }
  } else {
    error.value = 'Invalid authentication response.'
    isLoading.value = false
  }
})
</script>