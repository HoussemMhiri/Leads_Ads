<template>
  <div class="space-y-4">
    <p class="text-sm text-muted-foreground">Choose your preferred appearance.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <button
        v-for="option in themeOptions"
        :key="option.value"
        type="button"
        @click="selected = option.value"
        :class="[
          'flex flex-col items-center gap-2 rounded-lg border p-3 text-sm transition-colors',
          selected === option.value
            ? 'border-primary bg-primary/5 text-primary font-medium'
            : 'border-border hover:bg-accent hover:text-accent-foreground text-muted-foreground',
        ]"
      >
        <component :is="option.icon" class="size-5 shrink-0" />
        <span>{{ option.label }}</span>
      </button>
    </div>
    <p class="text-xs text-muted-foreground">Theme switching coming soon.</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Sun, Moon, Monitor } from 'lucide-vue-next'

const themeOptions: { value: 'light' | 'dark' | 'system'; label: string; icon: typeof Sun }[] = [
  { value: 'light', label: 'Light', icon: Sun },
  { value: 'dark', label: 'Dark', icon: Moon },
  { value: 'system', label: 'System', icon: Monitor },
]

const selected = ref<'light' | 'dark' | 'system'>('system')
</script>
