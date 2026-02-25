<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Hello, {{ displayName }}!</h1>
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
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { storeToRefs } from 'pinia'

const router = useRouter()
const authStore = useAuthStore()
const employeeAuthStore = useEmployeeAuthStore()

const { authUser } = storeToRefs(authStore)
const { authEmployee } = storeToRefs(employeeAuthStore)

const displayName = computed(
  () => authEmployee.value?.name ?? authUser.value?.name ?? 'there',
)

const handleLogout = async () => {
  try {
    if (authEmployee.value) {
      await employeeAuthStore.logout()
      router.push({
        name: 'employeeSignin',
        query: { tenant: employeeAuthStore.workspaceName ?? undefined },
      })
    } else {
      await authStore.logout()
      router.push({ name: 'signin' })
    }
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
