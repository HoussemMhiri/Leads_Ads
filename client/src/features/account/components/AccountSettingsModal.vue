<template>
  <Dialog :open="open" @update:open="onDialogUpdate">
    <DialogContent
      class="p-0! gap-0! overflow-hidden rounded-none! w-full! h-dvh! max-w-full! top-0! left-0! translate-x-0! translate-y-0! sm:w-full! sm:h-[85vh]! sm:max-h-180! sm:min-h-125! sm:max-w-5xl! sm:rounded-lg! sm:top-1/2! sm:left-1/2! sm:-translate-x-1/2! sm:-translate-y-1/2!"
      :show-close-button="false"
    >
      <VisuallyHidden>
        <DialogTitle>Account Settings</DialogTitle>
      </VisuallyHidden>

      <div class="h-full w-full flex overflow-hidden">

        <!-- ── MOBILE: Sections menu ──────────────────────────────────── -->
        <div v-if="!mobileSection" class="flex flex-col w-full sm:hidden">
          <div class="flex items-center justify-between px-4 py-3 border-b shrink-0">
            <span class="text-sm font-semibold">Account Settings</span>
            <DialogClose class="shrink-0 flex rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity">
              <X class="size-4" />
              <span class="sr-only">Close</span>
            </DialogClose>
          </div>
          <nav class="flex-1 overflow-y-auto p-2 space-y-0.5">
            <button
              v-for="item in sections"
              :key="item.id"
              type="button"
              @click="mobileSection = item.id"
              class="w-full flex items-center justify-between gap-3 px-3 py-3 rounded-md text-sm transition-colors text-left hover:bg-accent hover:text-accent-foreground"
            >
              <div class="flex items-center gap-3">
                <component :is="item.icon" class="size-4 shrink-0 text-muted-foreground" />
                <span>{{ item.label }}</span>
              </div>
              <ChevronRight class="size-4 shrink-0 text-muted-foreground" />
            </button>
          </nav>
          <div class="p-2 border-t shrink-0">
            <button
              type="button"
              @click="showLogoutConfirm = true"
              class="w-full flex items-center gap-3 px-3 py-3 rounded-md text-sm transition-colors text-left text-destructive hover:bg-destructive/10"
            >
              <LogOut class="size-4 shrink-0" />
              <span>Log out</span>
            </button>
          </div>
        </div>

        <!-- ── MOBILE: Section content ────────────────────────────────── -->
        <div v-else class="flex flex-col w-full sm:hidden overflow-hidden">
          <div class="flex items-center gap-2 px-2 py-3 border-b shrink-0">
            <button
              type="button"
              @click="mobileSection = null"
              class="shrink-0 flex rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity"
            >
              <ChevronLeft class="size-4" />
              <span class="sr-only">Back</span>
            </button>
            <span class="text-sm font-semibold flex-1 truncate">{{ mobileSectionData?.label }}</span>
            <DialogClose class="shrink-0 flex rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity">
              <X class="size-4" />
              <span class="sr-only">Close</span>
            </DialogClose>
          </div>
          <div class="flex-1 overflow-y-auto p-4">
            <component :is="mobileSectionData?.component" />
          </div>
        </div>

        <!-- ── DESKTOP: Sidebar ───────────────────────────────────────── -->
        <aside class="hidden sm:flex w-56 shrink-0 border-r flex-col bg-muted/30">
          <div class="px-4 py-4 border-b">
            <span class="text-sm font-semibold text-foreground">Account Settings</span>
          </div>
          <nav class="flex-1 p-2 space-y-0.5 overflow-y-auto">
            <button
              v-for="item in sections"
              :key="item.id"
              type="button"
              @click="activeSection = item.id"
              :class="[
                'w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors text-left',
                activeSection === item.id
                  ? 'bg-primary/10 text-primary font-medium'
                  : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
              ]"
            >
              <component :is="item.icon" class="size-4 shrink-0" />
              <span>{{ item.label }}</span>
            </button>
          </nav>
          <div class="p-2 border-t shrink-0">
            <button
              type="button"
              @click="showLogoutConfirm = true"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors text-left text-destructive hover:bg-destructive/10"
            >
              <LogOut class="size-4 shrink-0" />
              <span>Log out</span>
            </button>
          </div>
        </aside>

        <!-- ── DESKTOP: Content area ──────────────────────────────────── -->
        <div class="hidden sm:flex flex-1 flex-col overflow-hidden min-h-0">
          <div class="flex items-center justify-between gap-3 px-6 py-4 border-b shrink-0">
            <div class="min-w-0 flex-1">
              <h3 class="text-base font-semibold truncate">{{ currentSection?.label }}</h3>
              <p class="text-sm text-muted-foreground">{{ currentSection?.description }}</p>
            </div>
            <DialogClose class="shrink-0 flex rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity">
              <X class="size-4" />
              <span class="sr-only">Close</span>
            </DialogClose>
          </div>
          <div class="flex-1 overflow-y-auto p-6">
            <component :is="currentSection?.component" />
          </div>
        </div>

      </div>
    </DialogContent>
  </Dialog>

  <LogoutConfirmModal :open="showLogoutConfirm" @update:open="showLogoutConfirm = $event" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { UserCircle, CreditCard, Palette, ShieldCheck, Bell, X, ChevronRight, ChevronLeft, LogOut } from 'lucide-vue-next'
import { VisuallyHidden } from 'reka-ui'
import { Dialog, DialogContent, DialogClose, DialogTitle } from '@/components/ui/dialog'
import AccountInfoSection from './sections/AccountInfoSection.vue'
import BillingSection from './sections/BillingSection.vue'
import ThemeSection from './sections/ThemeSection.vue'
import SecuritySection from './sections/SecuritySection.vue'
import NotificationsSection from './sections/NotificationsSection.vue'
import LogoutConfirmModal from './LogoutConfirmModal.vue'

defineProps<{ open: boolean }>()
const emit = defineEmits<{ 'update:open': [value: boolean] }>()

const showLogoutConfirm = ref(false)

const sections = [
  {
    id: 'account-info',
    label: 'Account Settings',
    description: 'Manage your personal information',
    icon: UserCircle,
    component: AccountInfoSection,
  },
  {
    id: 'billing',
    label: 'Billing',
    description: 'Manage your subscription and billing',
    icon: CreditCard,
    component: BillingSection,
  },
  {
    id: 'theme',
    label: 'Theme',
    description: 'Customize the appearance of the app',
    icon: Palette,
    component: ThemeSection,
  },
  {
    id: 'security',
    label: 'Security',
    description: 'Manage your password and active sessions',
    icon: ShieldCheck,
    component: SecuritySection,
  },
  {
    id: 'notifications',
    label: 'Notifications',
    description: 'Control which alerts you receive',
    icon: Bell,
    component: NotificationsSection,
  },
]

// Desktop
const activeSection = ref('account-info')
const currentSection = computed(() => sections.find((s) => s.id === activeSection.value))

// Mobile: null = show menu, string = show that section's content
const mobileSection = ref<string | null>(null)
const mobileSectionData = computed(() => sections.find((s) => s.id === mobileSection.value))

const onDialogUpdate = (value: boolean) => {
  if (!value) mobileSection.value = null
  emit('update:open', value)
}
</script>
