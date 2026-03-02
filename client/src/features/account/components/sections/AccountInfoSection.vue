<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Avatar class="w-20 h-20">
        <AvatarImage v-if="userAvatar" :src="userAvatar" :alt="userName" />
        <AvatarFallback class="text-lg font-semibold bg-primary/10 text-primary">
          {{ userInitial }}
        </AvatarFallback>
      </Avatar>
      <div class="space-y-1">
        <p class="text-sm font-medium">Profile Photo</p>
        <p class="text-xs text-muted-foreground">Coming soon</p>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium">Name</label>
      <input
        type="text"
        :value="userName"
        disabled
        class="w-full px-3 py-2 rounded-md border bg-muted text-sm text-muted-foreground cursor-not-allowed"
      />
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium">Email</label>
      <input
        type="email"
        :value="userEmail"
        disabled
        class="w-full px-3 py-2 rounded-md border bg-muted text-sm text-muted-foreground cursor-not-allowed"
      />
    </div>

    <p class="text-xs text-muted-foreground">Editing account info coming soon.</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'

const authStore = useAuthStore()
const employeeAuthStore = useEmployeeAuthStore()
const { authUser } = storeToRefs(authStore)
const { authEmployee } = storeToRefs(employeeAuthStore)

const isEmployee = computed(() => authEmployee.value !== null)

const userName = computed(() =>
  isEmployee.value ? (authEmployee.value?.name ?? 'Employee') : (authUser.value?.name ?? 'User'),
)
const userEmail = computed(() =>
  isEmployee.value ? (authEmployee.value?.email ?? '') : (authUser.value?.email ?? ''),
)
const userAvatar = computed(() => (isEmployee.value ? null : (authUser.value?.avatar ?? null)))
const userInitial = computed(() => userName.value.charAt(0).toUpperCase())
</script>
