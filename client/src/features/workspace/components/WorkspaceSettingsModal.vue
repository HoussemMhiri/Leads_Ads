<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-4xl p-0 gap-0 overflow-hidden h-150" :show-close-button="false">
      <div class="flex h-full">
        <!-- ── Left sidebar ── -->
        <aside class="w-56 shrink-0 border-r flex flex-col bg-muted/30">
          <!-- Header -->
          <div class="px-4 py-4 border-b">
            <DialogTitle class="text-sm font-semibold text-foreground">Workspace Settings</DialogTitle>
          </div>

          <!-- Nav items -->
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

        <!-- ── Right content ── -->
        <div class="flex-1 flex flex-col overflow-hidden">
          <!-- Content header -->
          <div class="flex items-center justify-between px-6 py-4 border-b shrink-0">
            <div>
              <h3 class="text-base font-semibold">{{ currentSection?.label }}</h3>
              <p class="text-sm text-muted-foreground">{{ currentSection?.description }}</p>
            </div>
            <DialogClose class="rounded-md opacity-70 hover:opacity-100 transition-opacity">
              <X class="size-4" />
              <span class="sr-only">Close</span>
            </DialogClose>
          </div>

          <!-- Content body -->
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
import { Building2, ShieldCheck, Users, Globe, CalendarDays, Mail, X } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogClose, DialogTitle } from '@/components/ui/dialog'
import WorkspaceInfoSection from './sections/WorkspaceInfoSection.vue'
import RolesSection from './sections/RolesSection.vue'
import TeamSection from './sections/TeamSection.vue'
import LanguageRegionSection from './sections/LanguageRegionSection.vue'
import CalendarSettingsSection from './sections/CalendarSettingsSection.vue'
import EmailPreferencesSection from './sections/EmailPreferencesSection.vue'

defineProps<{ open: boolean }>()
defineEmits<{ 'update:open': [value: boolean] }>()

// ── Sections definition ───────────────────────────────────────────────────────
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

const activeSection = ref('workspace-info')

const currentSection = computed(() => sections.find((s) => s.id === activeSection.value))
</script>
