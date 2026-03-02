<template>
  <div class="space-y-8">

    <div v-for="group in notificationGroups" :key="group.title" class="space-y-4">
      <div>
        <h4 class="text-sm font-semibold">{{ group.title }}</h4>
        <p class="text-xs text-muted-foreground mt-0.5">{{ group.description }}</p>
      </div>

      <div class="rounded-lg border divide-y">
        <div
          v-for="item in group.items"
          :key="item.id"
          class="flex items-center justify-between gap-4 px-4 py-3"
        >
          <div class="min-w-0">
            <p class="text-sm font-medium">{{ item.label }}</p>
            <p class="text-xs text-muted-foreground">{{ item.description }}</p>
          </div>
          <div class="flex items-center gap-4 shrink-0">
            <label class="flex items-center gap-1.5 cursor-not-allowed opacity-50">
              <input type="checkbox" :checked="item.inApp" disabled class="rounded" />
              <span class="text-xs text-muted-foreground">In-app</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-not-allowed opacity-50">
              <input type="checkbox" :checked="item.email" disabled class="rounded" />
              <span class="text-xs text-muted-foreground">Email</span>
            </label>
          </div>
        </div>
      </div>

      <Separator v-if="group !== notificationGroups[notificationGroups.length - 1]" />
    </div>

    <p class="text-xs text-muted-foreground">Notification preferences coming soon.</p>

  </div>
</template>

<script setup lang="ts">
import { Separator } from '@/components/ui/separator'

const notificationGroups = [
  {
    title: 'Leads',
    description: 'Alerts related to incoming leads.',
    items: [
      {
        id: 'lead-new',
        label: 'New lead received',
        description: 'When a new lead is captured from any campaign.',
        inApp: true,
        email: true,
      },
      {
        id: 'lead-assigned',
        label: 'Lead assigned to you',
        description: 'When a lead is assigned to your account.',
        inApp: true,
        email: false,
      },
    ],
  },
  {
    title: 'Campaigns',
    description: 'Updates on your ad campaigns.',
    items: [
      {
        id: 'campaign-status',
        label: 'Campaign status changes',
        description: 'When a campaign starts, pauses, or ends.',
        inApp: true,
        email: false,
      },
      {
        id: 'campaign-budget',
        label: 'Budget limit reached',
        description: 'When a campaign hits its daily or total budget.',
        inApp: true,
        email: true,
      },
    ],
  },
  {
    title: 'Team',
    description: 'Activity within your workspace.',
    items: [
      {
        id: 'team-member-joined',
        label: 'Member joined',
        description: 'When a new member accepts a workspace invitation.',
        inApp: true,
        email: false,
      },
      {
        id: 'team-role-changed',
        label: 'Role changed',
        description: 'When your role in the workspace is updated.',
        inApp: true,
        email: true,
      },
    ],
  },
  {
    title: 'Billing',
    description: 'Alerts about your subscription and payments.',
    items: [
      {
        id: 'billing-payment-failed',
        label: 'Payment failed',
        description: 'When a payment attempt is unsuccessful.',
        inApp: true,
        email: true,
      },
      {
        id: 'billing-report',
        label: 'Weekly performance report',
        description: 'A summary of your campaigns sent every Monday.',
        inApp: false,
        email: true,
      },
    ],
  },
]
</script>
