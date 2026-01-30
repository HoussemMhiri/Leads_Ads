<template>
  <div v-if="message" class="p-3 rounded-lg border" :class="containerClass" role="alert">
    <p class="text-sm" :class="textClass">
      {{ message }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type AlertType = 'error' | 'success' | 'info' | 'warning'

const props = defineProps<{
  message?: string | null
  type?: AlertType
}>()

const type = computed(() => props.type ?? 'error')

const containerClass = computed(() => {
  switch (type.value) {
    case 'success':
      return 'bg-green-50 border-green-200'
    case 'warning':
      return 'bg-yellow-50 border-yellow-200'
    case 'info':
      return 'bg-blue-50 border-blue-200'
    default:
      return 'bg-red-50 border-red-200'
  }
})

const textClass = computed(() => {
  switch (type.value) {
    case 'success':
      return 'text-green-800'
    case 'warning':
      return 'text-yellow-800'
    case 'info':
      return 'text-blue-800'
    default:
      return 'text-red-600'
  }
})
</script>
