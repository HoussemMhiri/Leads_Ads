<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import type { ButtonVariants } from '.'
import { Primitive } from 'reka-ui'
import { cn } from '@/lib/utils'
import { buttonVariants } from '.'
import { Spinner } from '@/components/ui/spinner'

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  class?: HTMLAttributes['class']
  loading?: boolean
  disabled?: boolean
  spinnerSize?: number
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  loading: false,
  disabled: false,
  spinnerSize: 16,
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), props.class)"
    :disabled="loading || disabled"
  >
    <Spinner v-if="loading" class="mr-2" :size="spinnerSize" />
    <slot />
  </Primitive>
</template>
