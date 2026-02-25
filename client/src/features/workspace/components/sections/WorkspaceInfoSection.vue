<template>
  <div class="space-y-6">
    <!-- Logo upload -->
    <div class="flex items-center gap-4">
      <div class="relative group cursor-pointer" @click="triggerFileInput">
        <Avatar class="w-20 h-20">
          <AvatarImage v-if="logoPreview" :src="logoPreview" alt="Workspace logo" />
          <AvatarFallback class="text-lg font-semibold">
            {{ workspaceInitials }}
          </AvatarFallback>
        </Avatar>
        <!-- Hover overlay -->
        <div
          class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <Camera class="w-5 h-5 text-white" />
        </div>
        <input
          ref="fileInput"
          type="file"
          accept="image/jpg,image/jpeg,image/png,image/webp"
          class="hidden"
          @change="onFileChange"
        />
      </div>
      <div class="space-y-1">
        <p class="text-sm font-medium">Workspace Logo</p>
        <p class="text-xs text-muted-foreground">JPG, PNG or WebP · max 20 MB</p>
        <Button variant="outline" size="sm" type="button" @click="triggerFileInput">
          Change logo
        </Button>
        <Button
          v-if="logoFile"
          variant="ghost"
          size="sm"
          type="button"
          class="text-destructive hover:text-destructive ml-1"
          @click="clearLogo"
        >
          Remove
        </Button>
      </div>
    </div>

    <!-- Name field -->
    <div class="space-y-2">
      <label class="text-sm font-medium" for="workspace-name">Workspace Name</label>
      <input
        id="workspace-name"
        v-model="name"
        type="text"
        placeholder="My Workspace"
        class="w-full px-3 py-2 rounded-md border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        :class="{ 'border-destructive': nameError }"
      />
      <p v-if="nameError" class="text-xs text-destructive">{{ nameError }}</p>
    </div>

    <AlertMessage :message="successMessage" type="success" />
    <AlertMessage :message="errorMessage" type="error" />

    <div class="flex justify-end">
      <button
        type="button"
        class="w-full sm:w-auto px-4 py-2 rounded-md bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="workspaceStore.isLoading || !hasChanges"
        @click="submit"
      >
        {{ workspaceStore.isLoading ? 'Saving…' : 'Update' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useWorkspaceStore } from '@/features/workspace/store/workspace.store'
import AlertMessage from '@/components/shared/AlertMessage.vue'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Camera } from 'lucide-vue-next'

const workspaceStore = useWorkspaceStore()

const name = ref('')
const originalName = ref('')
const nameError = ref<string | null>(null)
const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const successMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)

const nameChanged = computed(() => name.value.trim() !== originalName.value)
const hasChanges = computed(() => nameChanged.value || logoFile.value !== null)
const workspaceInitials = computed(() => {
  const n = name.value.trim()
  if (!n) return '?'
  return n
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
})

watch(
  () => workspaceStore.name,
  (val) => {
    name.value = val ?? ''
    originalName.value = val ?? ''
  },
  { immediate: true },
)

watch(
  () => workspaceStore.logo,
  (val) => {
    if (!logoFile.value) logoPreview.value = val
  },
  { immediate: true },
)

function triggerFileInput() {
  fileInput.value?.click()
}

function revokeBlobPreview() {
  if (logoPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(logoPreview.value)
  }
}

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  revokeBlobPreview()
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

function clearLogo() {
  revokeBlobPreview()
  logoFile.value = null
  logoPreview.value = workspaceStore.logo
  if (fileInput.value) fileInput.value.value = ''
}

async function submit() {
  nameError.value = null
  successMessage.value = null
  errorMessage.value = null

  if (nameChanged.value && !name.value.trim()) {
    nameError.value = 'Workspace name cannot be empty.'
    return
  }

  const payload: { name?: string; logo?: File } = {}
  if (nameChanged.value) payload.name = name.value.trim()
  if (logoFile.value) payload.logo = logoFile.value

  try {
    const response = await workspaceStore.update(payload)
    successMessage.value = response.message
    originalName.value = workspaceStore.name ?? ''
    revokeBlobPreview()
    logoFile.value = null
    logoPreview.value = workspaceStore.logo
  } catch {
    errorMessage.value = workspaceStore.error ?? 'Something went wrong.'
  }
}

onBeforeUnmount(() => {
  revokeBlobPreview()
})
</script>
