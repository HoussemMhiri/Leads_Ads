<template>
  <Dialog :open="open" @update:open="onDialogUpdate">
    <DialogContent
      class="p-0! gap-0! overflow-hidden rounded-none! w-full! h-dvh! max-w-full! top-0! left-0! translate-x-0! translate-y-0! sm:w-full! sm:h-[85vh]! sm:max-h-180! sm:min-h-125! sm:max-w-5xl! sm:rounded-lg! sm:top-1/2! sm:left-1/2! sm:-translate-x-1/2! sm:-translate-y-1/2!"
      :show-close-button="false"
    >
      <VisuallyHidden>
        <DialogTitle>Workspace Settings</DialogTitle>
      </VisuallyHidden>

      <div class="h-full w-full flex overflow-hidden">

        <!-- ── MOBILE: Sections menu ──────────────────────────────────── -->
        <div v-if="!mobileSection" class="flex flex-col w-full sm:hidden">
          <div class="flex items-center justify-between px-4 py-3 border-b shrink-0">
            <span class="text-sm font-semibold">Workspace Settings</span>
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
            <span class="text-sm font-semibold text-foreground">Workspace Settings</span>
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
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Building2, ShieldCheck, Users, Globe, CalendarDays, Mail, X, ChevronRight, ChevronLeft } from 'lucide-vue-next'
import { VisuallyHidden } from 'reka-ui'
import { Dialog, DialogContent, DialogClose, DialogTitle } from '@/components/ui/dialog'
import WorkspaceInfoSection from './sections/WorkspaceInfoSection.vue'
import RolesSection from './sections/RolesSection.vue'
import TeamSection from './sections/TeamSection.vue'
import LanguageRegionSection from './sections/LanguageRegionSection.vue'
import CalendarSettingsSection from './sections/CalendarSettingsSection.vue'
import EmailPreferencesSection from './sections/EmailPreferencesSection.vue'

defineProps<{ open: boolean }>()
const emit = defineEmits<{ 'update:open': [value: boolean] }>()

const sections = [
  {
    id: 'workspace-info',
    label: 'Workspace Info',
    description: 'Manage your workspace name and details',
    icon: Building2,
    component: WorkspaceInfoSection,
  },
  {
    id: 'roles',
    label: 'Roles',
    description: 'Define roles and their permissions',
    icon: ShieldCheck,
    component: RolesSection,
  },
  {
    id: 'team',
    label: 'Team',
    description: 'Manage team members and invitations',
    icon: Users,
    component: TeamSection,
  },
  {
    id: 'language-region',
    label: 'Language & Region',
    description: 'Set your language and regional preferences',
    icon: Globe,
    component: LanguageRegionSection,
  },
  {
    id: 'calendar-settings',
    label: 'Calendar Settings',
    description: 'Configure your calendar and scheduling options',
    icon: CalendarDays,
    component: CalendarSettingsSection,
  },
  {
    id: 'email-preferences',
    label: 'Email Preferences',
    description: 'Control which emails you receive',
    icon: Mail,
    component: EmailPreferencesSection,
  },
]

// Desktop
const activeSection = ref('workspace-info')
const currentSection = computed(() => sections.find((s) => s.id === activeSection.value))

// Mobile: null = show menu, string = show that section's content
const mobileSection = ref<string | null>(null)
const mobileSectionData = computed(() => sections.find((s) => s.id === mobileSection.value))

const onDialogUpdate = (value: boolean) => {
  if (!value) mobileSection.value = null
  emit('update:open', value)
}
</script>
