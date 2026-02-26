import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { VueQueryPlugin } from '@tanstack/vue-query'

import App from './App.vue'
import router from './router'
import { useSessionStore } from './stores/session.store'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(VueQueryPlugin)


const sessionStore = useSessionStore()
sessionStore.init().then(() => {
  app.mount('#app')
})

