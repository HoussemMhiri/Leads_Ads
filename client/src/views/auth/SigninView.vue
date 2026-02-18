<template>
  <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12">
    <!-- Title -->
    <div class="text-center mb-6 sm:mb-8">
      <h1 class="text-3xl font-bold text-black">Welcome back to Leads</h1>
      <p class="mt-2 text-gray-600">Sign in and take control of your ad performance.</p>
    </div>

    <!-- Form -->
    <SigninForm />
  </div>
</template>

<script setup lang="ts">
import SigninForm from '@/features/auth/components/SigninForm.vue'
</script>

<style scoped></style>
