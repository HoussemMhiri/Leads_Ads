<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome, {{ authUser?.name }}!</h1>
        <p class="text-gray-600 mb-6">Your account has been successfully created.</p>

        <div class="flex gap-4">
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            Logout
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { storeToRefs } from 'pinia'

const router = useRouter()
const authStore = useAuthStore()
const { authUser } = storeToRefs(authStore)
const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push({ name: 'signin' })
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
