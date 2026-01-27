<template>
  <div v-if="message" class="p-3 rounded-lg bg-red-50 border border-red-200" role="alert">
    <p class="text-sm text-red-600">{{ message }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ message?: string | null }>()
</script>
