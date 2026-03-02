<template>
  <Sidebar collapsible="icon">
    <!-- ── Header: tenant logo + name ── -->
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" class="cursor-default">
            <Avatar class="size-8 rounded-lg">
              <AvatarImage v-if="workspaceStore.logo" :src="workspaceStore.logo" :alt="tenantName" />
              <AvatarFallback class="rounded-lg bg-primary text-primary-foreground font-bold text-sm">
                {{ tenantInitial }}
              </AvatarFallback>
            </Avatar>
            <div class="flex flex-col gap-0.5 leading-none overflow-hidden">
              <span class="font-semibold truncate">{{ tenantName }}</span>
              <span class="text-xs text-muted-foreground truncate">Workspace</span>
            </div>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <!-- ── Content: navigation ── -->
    <SidebarContent>
      <SidebarGroup>
        <SidebarGroupContent>
          <SidebarMenu>
            <template v-for="item in navItems" :key="item.title">
              <!-- Simple item (no children) -->
              <SidebarMenuItem v-if="!item.children">
                <SidebarMenuButton
                  v-if="item.to"
                  as-child
                  :tooltip="item.title"
                  :is-active="isRouteActive(item.to)"
                >
                  <RouterLink :to="item.to">
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                  </RouterLink>
                </SidebarMenuButton>
                <SidebarMenuButton v-else :tooltip="item.title" @click="item.action?.()">
                  <component :is="item.icon" />
                  <span>{{ item.title }}</span>
                </SidebarMenuButton>
              </SidebarMenuItem>

              <!-- Collapsible item (with children) -->
              <CollapsibleRoot
                v-else
                :open="openItems[item.title]"
                @update:open="openItems[item.title] = $event"
                class="group/collapsible"
              >
                <SidebarMenuItem>
                  <CollapsibleTrigger as-child>
                    <SidebarMenuButton :tooltip="item.title">
                      <component :is="item.icon" />
                      <span>{{ item.title }}</span>
                      <ChevronRight
                        class="ml-auto size-4 shrink-0 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                      />
                    </SidebarMenuButton>
                  </CollapsibleTrigger>
                  <CollapsibleContent>
                    <SidebarMenuSub>
                      <SidebarMenuSubItem v-for="child in item.children" :key="child.title">
                        <SidebarMenuSubButton
                          v-if="child.to"
                          as-child
                          :is-active="isRouteActive(child.to)"
                        >
                          <RouterLink :to="child.to">{{ child.title }}</RouterLink>
                        </SidebarMenuSubButton>
                        <SidebarMenuSubButton v-else>
                          {{ child.title }}
                        </SidebarMenuSubButton>
                      </SidebarMenuSubItem>
                    </SidebarMenuSub>
                  </CollapsibleContent>
                </SidebarMenuItem>
              </CollapsibleRoot>
            </template>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <!-- ── Footer: user info ── -->
    <SidebarFooter>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" @click="showAccountSettings = true">
            <Avatar class="size-8">
              <AvatarImage v-if="userAvatar" :src="userAvatar" :alt="userName" />
              <AvatarFallback class="bg-primary/10 text-primary font-semibold text-sm">
                {{ userInitial }}
              </AvatarFallback>
            </Avatar>
            <div class="flex flex-col gap-0.5 leading-none overflow-hidden">
              <span class="font-medium truncate">{{ userName }}</span>
              <span class="text-xs text-muted-foreground truncate">{{ userEmail }}</span>
            </div>
            <Settings class="ml-auto size-4 shrink-0 text-muted-foreground" />
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>

    <SidebarRail />

    <WorkspaceSettingsModal
      :open="showWorkspaceSettings"
      @update:open="showWorkspaceSettings = $event"
    />

    <AccountSettingsModal
      :open="showAccountSettings"
      @update:open="showAccountSettings = $event"
    />
  </Sidebar>
</template>

<script setup lang="ts">
import { reactive, computed, ref, type Component } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { CollapsibleRoot, CollapsibleTrigger, CollapsibleContent } from 'reka-ui'
import {
  LayoutDashboard,
  Megaphone,
  FolderOpen,
  CalendarDays,
  TrendingUp,
  Send,
  Share2,
  Bell,
  UserCircle,
  Settings,
  ChevronRight,
} from 'lucide-vue-next'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  SidebarRail,
} from '@/components/ui/sidebar'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { useEmployeeAuthStore } from '@/features/workspace/employee/store/employeeAuth.store'
import { useWorkspaceStore } from '@/features/workspace/store/workspace.store'
import { storeToRefs } from 'pinia'
import type { RouteLocationRaw } from 'vue-router'
import WorkspaceSettingsModal from '@/features/workspace/components/WorkspaceSettingsModal.vue'
import AccountSettingsModal from '@/features/account/components/AccountSettingsModal.vue'

// ── Modal state ───────────────────────────────────────────────────────────────
const showWorkspaceSettings = ref(false)
const showAccountSettings = ref(false)

// ── Auth ──────────────────────────────────────────────────────────────────────
const authStore = useAuthStore()
const employeeAuthStore = useEmployeeAuthStore()
const workspaceStore = useWorkspaceStore()
const { authUser } = storeToRefs(authStore)
const { authEmployee, workspaceName } = storeToRefs(employeeAuthStore)

const isEmployee = computed(() => authEmployee.value !== null)

const userName = computed(() =>
  isEmployee.value ? (authEmployee.value?.name ?? 'Employee') : (authUser.value?.name ?? 'User'),
)
const userEmail = computed(() =>
  isEmployee.value ? (authEmployee.value?.email ?? '') : (authUser.value?.email ?? ''),
)
const userAvatar = computed(() => (isEmployee.value ? null : (authUser.value?.avatar ?? null)))
const userInitial = computed(() => userName.value.charAt(0).toUpperCase())

// ── Workspace header ──────────────────────────────────────────────────────────
const tenantName = computed(
  () =>
    workspaceStore.name ??
    (isEmployee.value
      ? (workspaceName.value ?? 'My Workspace')
      : (authUser.value?.tenant?.subdomain ?? 'My Workspace')),
)
const tenantInitial = computed(() => tenantName.value.charAt(0).toUpperCase())

// ── Navigation definition ─────────────────────────────────────────────────────
interface NavChild {
  title: string
  to?: RouteLocationRaw
}
interface NavItem {
  title: string
  icon: Component
  to?: RouteLocationRaw
  children?: NavChild[]
  action?: () => void
}

const navItems: NavItem[] = [
  {
    title: 'Dashboard',
    icon: LayoutDashboard,
    to: { name: 'dashboard' },
  },
  {
    title: 'Ads',
    icon: Megaphone,
    children: [{ title: 'Meta Ads' }],
  },
  {
    title: 'Media Library',
    icon: FolderOpen,
  },
  {
    title: 'Calendar',
    icon: CalendarDays,
  },
  {
    title: 'Campaign Analytics',
    icon: TrendingUp,
    children: [{ title: 'Meta' }, { title: 'Google' }, { title: 'TikTok' }],
  },
  {
    title: 'Publishing',
    icon: Send,
    children: [{ title: 'Meta' }],
  },
  {
    title: 'Social Accounts',
    icon: Share2,
    children: [{ title: 'Meta' }],
  },
  {
    title: 'Notifications',
    icon: Bell,
  },
  {
    title: 'My Account',
    icon: UserCircle,
    action: () => {
      showAccountSettings.value = true
    },
  },
  {
    title: 'Workspace Settings',
    icon: Settings,
    action: () => {
      showWorkspaceSettings.value = true
    },
  },
]

// ── Collapsible state ─────────────────────────────────────────────────────────
const route = useRoute()

const openItems = reactive<Record<string, boolean>>(
  Object.fromEntries(
    navItems
      .filter((item) => item.children)
      .map((item) => [
        item.title,
        item.children!.some((child) => child.to && route.path === child.to),
      ]),
  ),
)

// ── Active route helper ───────────────────────────────────────────────────────
function isRouteActive(to?: RouteLocationRaw): boolean {
  if (!to) return false
  if (typeof to === 'string') return route.path === to
  if ('name' in to && to.name) return route.name === to.name
  if ('path' in to && to.path) return route.path === to.path
  return false
}
</script>
