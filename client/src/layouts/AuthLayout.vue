<template>
  <div class="min-h-screen flex">
    <img
      src="/images/businessman (2).jpg"
      alt="Auth"
      class="hidden md:flex w-[35%] bg-cover bg-center"
    />

    <div class="flex-1 flex items-center justify-center">
      <RouterView />
    </div>
  </div>
</template>

<script setup lang="ts"></script>
