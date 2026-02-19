<template>
  <SidebarProvider>
    <AppSidebar />

    <!-- ── Main area ── -->
    <SidebarInset>
      <!-- Top bar -->
      <header class="flex h-12 shrink-0 items-center gap-2 border-b px-4 sticky top-0 bg-background z-10">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="h-4 mx-1" />
        <slot name="header" />
      </header>

      <!-- Page content -->
      <div class="flex flex-1 flex-col p-4 md:p-6">
        <RouterView />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Separator } from '@/components/ui/separator'
import AppSidebar from '@/components/navigation/AppSidebar.vue'
</script>
