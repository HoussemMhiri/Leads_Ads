<template>
  <div class="space-y-8">

    <!-- ── Change Password ─────────────────────────────────────────── -->
    <div class="space-y-4">
      <div>
        <h4 class="text-sm font-semibold">Change Password</h4>
        <p class="text-xs text-muted-foreground mt-0.5">Update your account password.</p>
      </div>

      <div class="space-y-3">
        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="current-password">Current Password</label>
          <input
            id="current-password"
            v-model="form.currentPassword"
            type="password"
            placeholder="••••••••"
            class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="new-password">New Password</label>
          <input
            id="new-password"
            v-model="form.newPassword"
            type="password"
            placeholder="••••••••"
            class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium" for="confirm-password">Confirm New Password</label>
          <input
            id="confirm-password"
            v-model="form.confirmPassword"
            type="password"
            placeholder="••••••••"
            class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
          />
        </div>
      </div>

      <div class="flex justify-end">
        <button
          type="button"
          disabled
          class="px-4 py-2 rounded-md bg-primary text-primary-foreground text-sm font-medium opacity-50 cursor-not-allowed"
        >
          Update Password
        </button>
      </div>
      <p class="text-xs text-muted-foreground">Password update coming soon.</p>
    </div>

    <Separator />

    <!-- ── Two-Factor Authentication ──────────────────────────────── -->
    <div class="space-y-4">
      <div>
        <h4 class="text-sm font-semibold">Two-Factor Authentication</h4>
        <p class="text-xs text-muted-foreground mt-0.5">Add an extra layer of security to your account.</p>
      </div>

      <div class="flex items-center justify-between rounded-lg border p-4">
        <div class="flex items-center gap-3">
          <ShieldCheck class="size-5 text-muted-foreground shrink-0" />
          <div>
            <p class="text-sm font-medium">Authenticator App</p>
            <p class="text-xs text-muted-foreground">Use an app like Google Authenticator or Authy.</p>
          </div>
        </div>
        <span class="text-xs text-muted-foreground italic">Coming soon</span>
      </div>
    </div>

    <Separator />

    <!-- ── Active Sessions ─────────────────────────────────────────── -->
    <div class="space-y-4">
      <div>
        <h4 class="text-sm font-semibold">Active Sessions</h4>
        <p class="text-xs text-muted-foreground mt-0.5">Devices currently logged into your account.</p>
      </div>

      <div class="rounded-lg border divide-y">
        <div
          v-for="session in mockSessions"
          :key="session.id"
          class="flex items-center justify-between p-4 gap-4"
        >
          <div class="flex items-center gap-3 min-w-0">
            <component :is="session.icon" class="size-5 text-muted-foreground shrink-0" />
            <div class="min-w-0">
              <p class="text-sm font-medium truncate">{{ session.device }}</p>
              <p class="text-xs text-muted-foreground truncate">{{ session.location }} · {{ session.lastActive }}</p>
            </div>
          </div>
          <span
            v-if="session.current"
            class="text-xs font-medium text-primary bg-primary/10 px-2 py-0.5 rounded-full shrink-0"
          >
            Current
          </span>
          <button
            v-else
            type="button"
            disabled
            class="text-xs text-destructive hover:underline disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
          >
            Revoke
          </button>
        </div>
      </div>
      <p class="text-xs text-muted-foreground">Session management coming soon.</p>
    </div>

  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { Monitor, Smartphone, ShieldCheck } from 'lucide-vue-next'
import { Separator } from '@/components/ui/separator'

const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const mockSessions = [
  {
    id: 1,
    device: 'Chrome on Windows',
    location: 'Algiers, DZ',
    lastActive: 'Now',
    current: true,
    icon: Monitor,
  },
  {
    id: 2,
    device: 'Safari on iPhone',
    location: 'Algiers, DZ',
    lastActive: '2 hours ago',
    current: false,
    icon: Smartphone,
  },
]
</script>
