<template>
  <div class="max-w-md w-full bg-white px-6 py-8 sm:py-10 md:py-12">
    <!-- Title -->
    <div class="text-center mb-6 sm:mb-8">
      <h1 class="text-3xl font-bold text-black">Forgot Password</h1>
    </div>

    <p class="my-2 text-gray-600">Email to send reset instructions to</p>
    <!-- Form -->
    <ForgetpasswordForm />
  </div>
</template>

<script setup lang="ts">
import ForgetpasswordForm from '@/features/auth/components/ForgetpasswordForm.vue'
</script>

<style lang="scss" scoped></style>
