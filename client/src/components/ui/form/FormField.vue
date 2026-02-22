<script setup lang="ts">
import { Field as VeeField } from 'vee-validate'

defineProps<{ name: string }>()
</script>

<template>
  <VeeField v-slot="slotProps" :name="name">
    <slot v-bind="slotProps" />
  </VeeField>
</template>
