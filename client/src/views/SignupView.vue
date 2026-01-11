<template>
  <div class="max-w-md w-full p-6 bg-white">
    <!-- Title -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-black">Create your account</h1>
      <p class="mt-2 text-gray-600">Join now and start optimizing your ads with ease.</p>
    </div>

    <!-- Form -->
    <SignupForm />
  </div>
</template>

<script setup lang="ts">
import SignupForm from '@/features/auth/components/SignupForm.vue'
</script>
