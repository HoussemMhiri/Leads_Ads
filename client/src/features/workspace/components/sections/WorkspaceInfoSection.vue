<template>
  <div class="space-y-4">
    <div class="space-y-2">
      <label class="text-sm font-medium">Workspace Name</label>
      <input
        type="text"
        placeholder="My Workspace"
        class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium">Subdomain</label>
      <input
        type="text"
        placeholder="my-workspace"
        class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
      />
    </div>
    <div class="flex justify-end">
      <button
        type="button"
        class="px-4 py-2 rounded-md bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors"
      >
        Update
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
</script>
