<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="max-w-lg w-full space-y-6 text-center">
      <!-- Email Icon -->
      <div class="flex justify-center">
        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
          <EmailIcon class="w-8 h-8 text-primary" />
        </div>
      </div>

      <!-- Title -->
      <div>
        <h1 class="text-3xl font-bold text-foreground">Verify your email</h1>
        <p class="mt-2 text-muted-foreground">
          We sent a verification link to
          <span class="font-semibold text-foreground">{{ displayEmail }}</span>
        </p>
      </div>

      <!-- Instructions -->
      <div class="bg-muted/50 rounded-lg p-4 text-sm text-muted-foreground text-left">
        <p class="font-medium text-foreground mb-2">What to do next:</p>
        <ol class="list-decimal list-inside space-y-1">
          <li>Check your email inbox</li>
          <li>Click the verification link in the email</li>
          <li>Return here to log in</li>
        </ol>
      </div>

      <!-- Success Message -->
      <AlertMessage v-if="successMessage" :message="successMessage" type="success" />

      <!-- Error Message -->
      <AlertMessage v-if="error" :message="error" type="error" />

      <!-- Resend Button -->
      <div class="space-y-3">
        <p class="text-sm text-muted-foreground">Didn't receive the email?</p>
        <Button
          type="button"
          @click="handleResend"
          :disabled="isLoading || cooldown > 0"
          :loading="isLoading"
          variant="outline"
          class="w-full"
        >
          {{ cooldown > 0 ? `Resend in ${cooldown}s` : 'Resend verification email' }}
        </Button>
      </div>

      <!-- Back to Login -->
      <div class="pt-4">
        <RouterLink
          :to="{ name: 'signin' }"
          class="text-primary font-medium hover:underline cursor-pointer"
        >
          Back to login
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { storeToRefs } from 'pinia'
import Button from '@/components/ui/button/Button.vue'
import EmailIcon from '@/assets/icons/EmailIcon.vue'
import AlertMessage from '@/components/shared/AlertMessage.vue'

const authStore = useAuthStore()
const { isLoading, error, successMessage, registrationEmail } = storeToRefs(authStore)

const cooldown = ref(0)
let cooldownInterval: number | null = null

const displayEmail = computed(() => registrationEmail.value || 'your email')

const handleResend = async () => {
  if (!registrationEmail.value) return

  authStore.clearMessages()

  try {
    await authStore.resendVerificationEmail(registrationEmail.value)
    startCooldown()
  } catch (err) {
    console.error('Failed to resend verification email:', err)
  }
}

const startCooldown = () => {
  cooldown.value = 60
  cooldownInterval = window.setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0 && cooldownInterval) {
      clearInterval(cooldownInterval)
      cooldownInterval = null
    }
  }, 1000)
}

onBeforeUnmount(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
  }
})
</script>
