<template>
  <Transition
    enter-active-class="transition-all duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <div
      v-if="message && !dismissed"
      class="flex items-start justify-between gap-2 p-3 rounded-lg border"
      :class="containerClass"
      role="alert"
    >
      <ul v-if="lines.length > 1" class="text-sm list-disc list-inside space-y-1 flex-1" :class="textClass">
        <li v-for="(line, i) in lines" :key="i">{{ line }}</li>
      </ul>
      <p v-else class="text-sm flex-1" :class="textClass">{{ message }}</p>

      <button
        type="button"
        @click="dismiss"
        class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
        :class="textClass"
        aria-label="Dismiss"
      >
        <X class="size-3.5" />
      </button>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { X } from 'lucide-vue-next'

type AlertType = 'error' | 'success' | 'info' | 'warning'

const props = defineProps<{
  message?: string | null
  type?: AlertType
}>()

const emit = defineEmits<{ dismiss: [] }>()

const dismissed = ref(false)

// Reset dismissed whenever a new message arrives
watch(
  () => props.message,
  (val) => { if (val) dismissed.value = false },
)

const dismiss = () => {
  dismissed.value = true
  emit('dismiss')
}

const type = computed(() => props.type ?? 'error')

const lines = computed(() =>
  props.message ? props.message.split('\n').filter(Boolean) : [],
)

const containerClass = computed(() => {
  switch (type.value) {
    case 'success': return 'bg-green-50 border-green-200'
    case 'warning': return 'bg-yellow-50 border-yellow-200'
    case 'info':    return 'bg-blue-50 border-blue-200'
    default:        return 'bg-red-50 border-red-200'
  }
})

const textClass = computed(() => {
  switch (type.value) {
    case 'success': return 'text-green-800'
    case 'warning': return 'text-yellow-800'
    case 'info':    return 'text-blue-800'
    default:        return 'text-red-600'
  }
})
</script>
