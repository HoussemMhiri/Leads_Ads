<template>
  <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12">
    <!-- Title -->
    <div class="text-center mb-6 sm:mb-8">
      <h1 class="text-3xl font-bold text-black">Welcome back to Leads</h1>
      <p class="mt-2 text-gray-600">Sign in and take control of your ad performance.</p>
    </div>

    <!-- Form -->
    <SigninForm />

    <p class="mt-6 text-center text-sm text-gray-500">
      Are you a team member?
      <RouterLink :to="{ name: 'employeeSignin' }" class="text-blue-600 hover:underline">
        Sign in to your workspace
      </RouterLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import SigninForm from '@/features/auth/components/SigninForm.vue'
</script>

<style scoped></style>
