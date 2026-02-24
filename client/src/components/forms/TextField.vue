<script setup lang="ts">
import { ref, computed } from 'vue'
import FormField from '@/components/ui/form/FormField.vue'
import { Field, FieldLabel, FieldError, FieldDescription } from '@/components/ui/field'
import Input from '@/components/ui/input/Input.vue'
import EyeIcon from '@/assets/icons/EyeIcon.vue'
import EyeOffIcon from '@/assets/icons/EyeOffIcon.vue'

const props = defineProps<{
  name: string
  label: string
  type?: string
  placeholder?: string
  description?: string
  disabled?: boolean
}>()

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type === 'password') return showPassword.value ? 'text' : 'password'
  return props.type ?? 'text'
})
</script>

<template>
  <FormField :name="name" v-slot="{ field, errors }">
    <Field :data-invalid="!!errors.length">
      <FieldLabel :for="name">
        {{ label }}
      </FieldLabel>

      <div class="relative">
        <Input
          :id="name"
          :model-value="field.value"
          @update:model-value="field.onChange"
          @blur="field.onBlur"
          :type="inputType"
          :placeholder="placeholder"
          :aria-invalid="!!errors.length"
          :disabled="disabled"
          :class="type === 'password' ? 'pr-10' : ''"
        />

        <button
          v-if="type === 'password' && field.value"
          type="button"
          @click="showPassword = !showPassword"
          class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground"
          tabindex="-1"
        >
          <EyeIcon v-if="!showPassword" />
          <EyeOffIcon v-else />
        </button>
      </div>

      <FieldDescription v-if="description">
        {{ description }}
      </FieldDescription>

      <FieldError v-if="errors.length" :errors="errors" />
    </Field>
  </FormField>
</template>
