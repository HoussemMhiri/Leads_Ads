<template>
  <div class="h-screen flex overflow-hidden">
    <!-- Left image -->
    <img src="/images/leads.jpg" alt="Auth" class="hidden md:block w-[35%] h-full object-cover" />

    <!-- Right content -->
    <div class="flex-1 flex items-start justify-center px-4 overflow-y-auto py-8 min-h-full">
      <RouterView />
    </div>
  </div>
</template>

<script setup lang="ts"></script>
