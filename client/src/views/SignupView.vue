<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white">
    <!-- Title -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-black">Create your account</h1>
      <p class="mt-2 text-gray-600">Join now and start optimizing your ads with ease.</p>
    </div>

    <!-- Form -->
    <form class="space-y-4">
      <!-- Name -->
      <div>
        <label for="name" class="block mb-1 font-medium text-gray-700">Name</label>
        <Input id="name" placeholder="Your Name" class="w-full" />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block mb-1 font-medium text-gray-700">Email</label>
        <Input id="email" type="email" placeholder="you@example.com" class="w-full" />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block mb-1 font-medium text-gray-700">Password</label>
        <Input id="password" type="password" placeholder="Enter password" class="w-full" />
      </div>

      <!-- Confirm Password -->
      <div>
        <Label for="confirm-password" class="block mb-2 font-medium text-gray-700"
          >Confirm Password</Label
        >
        <Input
          id="confirm-password"
          type="password"
          placeholder="Confirm password"
          class="w-full"
        />
      </div>

      <!-- Submit -->
      <Button type="submit" class="w-full mt-4 h-10 rounded-2xl"> Sign Up </Button>
    </form>
  </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
</script>

<style scoped></style>
