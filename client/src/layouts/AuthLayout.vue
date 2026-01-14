<template>
  <div class="h-screen flex overflow-hidden [@media(max-width:380px)]:overflow-y-auto">
    <!-- Left image -->
    <img
      src="/images/businessman (2).jpg"
      alt="Auth"
      class="hidden md:block w-[35%] h-full object-cover"
    />

    <!-- Right content -->
    <div class="flex-1 flex items-center justify-center px-4">
      <RouterView />
    </div>
  </div>
</template>

<script setup lang="ts"></script>
