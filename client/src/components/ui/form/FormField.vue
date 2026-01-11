<script setup lang="ts">
import { Field as VeeField } from 'vee-validate'
</script>

<template>
  <VeeField v-slot="slotProps" name="fieldName">
    <slot v-bind="slotProps" />
  </VeeField>
</template>
