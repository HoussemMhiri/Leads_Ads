<script setup lang="ts">
import FormField from '@/components/ui/form/FormField.vue'
import { Field, FieldLabel, FieldError, FieldDescription } from '@/components/ui/field'
import Input from '@/components/ui/input/Input.vue'

defineProps<{
  name: string
  label: string
  type?: string
  placeholder?: string
  description?: string
}>()
</script>

<template>
  <FormField :name="name" v-slot="{ field, errors }">
    <Field :data-invalid="!!errors.length">
      <FieldLabel :for="name">
        {{ label }}
      </FieldLabel>

      <Input
        :id="name"
        :model-value="field.value"
        @update:model-value="field.onChange"
        @blur="field.onBlur"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        :aria-invalid="!!errors.length"
      />

      <FieldDescription v-if="description">
        {{ description }}
      </FieldDescription>

      <FieldError v-if="errors.length" :errors="errors" />
    </Field>
  </FormField>
</template>
