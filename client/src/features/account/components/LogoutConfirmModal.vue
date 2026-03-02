<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-sm">
      <DialogHeader>
        <DialogTitle>Log out</DialogTitle>
        <DialogDescription>
          Are you sure you want to log out of your account?
        </DialogDescription>
      </DialogHeader>

      <DialogFooter>
        <Button variant="outline" :disabled="isPending" @click="emit('update:open', false)">
          Cancel
        </Button>
        <Button variant="destructive" :disabled="isPending" @click="handleLogout">
          {{ isPending ? 'Logging out…' : 'Yes, log out' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

defineProps<{ open: boolean }>()

const router = useRouter()
const authStore = useAuthStore()
const employeeAuthStore = useEmployeeAuthStore()
const { authEmployee } = storeToRefs(employeeAuthStore)

const isEmployee = computed(() => authEmployee.value !== null)
const isPending = ref(false)

async function handleLogout() {
  isPending.value = true
  try {
    if (isEmployee.value) {
      await employeeAuthStore.logout()
      router.push({ name: 'employeeSignin' })
    } else {
      await authStore.logout()
      router.push({ name: 'signin' })
    }
    emit('update:open', false)
  } finally {
    isPending.value = false
  }
}
</script>
