import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/plugins/api'


export const useSessionStore = defineStore('session', () => {
  // State
  const csrfReady = ref(false)

  // Actions
  async function init() {
    if (csrfReady.value) return

    await api.get('/api/csrf-cookie')
    csrfReady.value = true
  }

  return {
    csrfReady,
    init,
  }
})