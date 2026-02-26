<template>
  <div class="space-y-4">

    <!-- Loading skeleton -->
    <div v-if="isLoading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="border rounded-md">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="flex items-center gap-2">
            <Skeleton class="h-4 w-20" />
            <Skeleton class="h-4 w-6 rounded-full" />
          </div>
          <Skeleton class="h-4 w-4" />
        </div>
        <div class="divide-y">
          <div v-for="j in 2" :key="j" class="flex items-center gap-3 px-4 py-3">
            <Skeleton class="size-8 rounded-full shrink-0" />
            <div class="flex-1 space-y-1.5">
              <Skeleton class="h-3 w-32" />
              <Skeleton class="h-3 w-48" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error state -->
    <AlertMessage v-else-if="isError" :message="errorMessage" type="error" />

    <!-- Members list -->
    <template v-else>
      <Accordion
        v-if="roleGroups.length > 0"
        type="multiple"
        :default-value="allGroupKeys"
        class="border rounded-md"
      >
        <AccordionItem
          v-for="group in roleGroups"
          :key="group.role"
          :value="group.role"
          class="px-0"
        >
          <AccordionTrigger class="px-4 py-3 hover:no-underline bg-muted/60 hover:bg-muted rounded-none">
            <div class="flex items-center gap-2">
              <span class="font-medium capitalize text-sm">{{ group.role }}</span>
              <span class="text-xs text-muted-foreground bg-muted px-1.5 py-0.5 rounded-full leading-none">
                {{ group.members.length }}
              </span>
            </div>
          </AccordionTrigger>

          <AccordionContent class="pb-0">
            <div class="divide-y border-t">
              <div
                v-for="member in group.members"
                :key="member.id"
                class="flex items-center gap-3 px-4 py-3"
              >
                <Avatar class="size-8 shrink-0">
                  <AvatarImage v-if="member.avatar" :src="member.avatar" :alt="member.name" />
                  <AvatarFallback class="text-xs font-medium">
                    {{ getInitials(member.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-medium leading-tight truncate">{{ member.name }}</p>
                    <span
                      v-if="member.status === 'pending'"
                      class="text-[10px] font-medium text-amber-600 bg-amber-50 dark:bg-amber-950 dark:text-amber-400 px-1.5 py-0.5 rounded-full shrink-0 leading-none"
                    >
                      Pending
                    </span>
                  </div>
                  <p class="text-xs text-muted-foreground truncate mt-0.5">{{ member.email }}</p>
                </div>

                <!-- 3-dot menu — hidden for the owner synthetic row -->
                <DropdownMenu v-if="!isOwnerRow(member.id)">
                  <DropdownMenuTrigger as-child>
                    <button
                      class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground transition-colors shrink-0"
                      @click.stop
                    >
                      <MoreHorizontal class="size-4" />
                    </button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-40">
                    <DropdownMenuItem @click="openUpdateRole(member)">
                      Update role
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      class="text-destructive focus:text-destructive"
                      @click="openRemove(member)"
                    >
                      Leave workspace
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>

      <p v-else class="text-sm text-muted-foreground text-center py-8">
        No team members yet.
      </p>
    </template>

    <!-- Invite Member button -->
    <Button class="w-full" @click="isInviteOpen = true">
      <UserPlus class="size-4 mr-2" />
      Invite Member
    </Button>

    <InviteMemberModal v-model:open="isInviteOpen" />
    <UpdateRoleModal v-model:open="isUpdateRoleOpen" :member="selectedMember" />
    <RemoveMemberModal v-model:open="isRemoveOpen" :member="selectedMember" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { UserPlus, MoreHorizontal } from 'lucide-vue-next'
import { useMembers } from '@/features/workspace/employee/composables/useMembers'
import { useAuthStore } from '@/features/auth/store/auth.store'
import { parseApiError } from '@/utils/handleApiError'
import type { TeamMember } from '@/features/workspace/employee/types/employee.types'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import InviteMemberModal from './InviteMemberModal.vue'
import UpdateRoleModal from './UpdateRoleModal.vue'
import RemoveMemberModal from './RemoveMemberModal.vue'

const ROLE_ORDER = ['owner', 'admin', 'manager', 'member', 'employee']

interface DisplayMember {
  id: number | string
  name: string
  email: string
  role: string
  avatar: string | null
  status: 'active' | 'pending'
}

interface RoleGroup {
  role: string
  members: DisplayMember[]
}

const authStore = useAuthStore()
const { authUser } = storeToRefs(authStore)

const { data: membersData, isLoading, isError, error } = useMembers()

const errorMessage = computed(() =>
  error.value ? parseApiError(error.value).message : 'Failed to load members.',
)

const isInviteOpen = ref(false)
const isUpdateRoleOpen = ref(false)
const isRemoveOpen = ref(false)
const selectedMember = ref<TeamMember | null>(null)

const getInitials = (name: string): string => {
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const isOwnerRow = (id: number | string): boolean =>
  typeof id === 'string' && id.startsWith('owner-')

const openUpdateRole = (member: DisplayMember) => {
  if (typeof member.id !== 'number') return
  selectedMember.value = member as TeamMember
  isUpdateRoleOpen.value = true
}

const openRemove = (member: DisplayMember) => {
  if (typeof member.id !== 'number') return
  selectedMember.value = member as TeamMember
  isRemoveOpen.value = true
}

const roleGroups = computed((): RoleGroup[] => {
  const groupMap = new Map<string, RoleGroup>()

  if (authUser.value) {
    groupMap.set('owner', {
      role: 'owner',
      members: [
        {
          id: `owner-${authUser.value.id}`,
          name: authUser.value.name,
          email: authUser.value.email,
          role: 'owner',
          avatar: authUser.value.avatar ?? null,
          status: 'active',
        },
      ],
    })
  }

  for (const member of membersData.value ?? []) {
    const role = member.role ?? 'member'
    if (!groupMap.has(role)) {
      groupMap.set(role, { role, members: [] })
    }
    groupMap.get(role)!.members.push(member)
  }

  return Array.from(groupMap.values()).sort((a, b) => {
    const ai = ROLE_ORDER.indexOf(a.role)
    const bi = ROLE_ORDER.indexOf(b.role)
    if (ai === -1 && bi === -1) return a.role.localeCompare(b.role)
    if (ai === -1) return 1
    if (bi === -1) return -1
    return ai - bi
  })
})

const allGroupKeys = computed(() => roleGroups.value.map((g) => g.role))
</script>
