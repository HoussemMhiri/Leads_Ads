import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useSessionStore } from './stores/session.store'

const app = createApp(App)

app.use(createPinia())
app.use(router)


const sessionStore = useSessionStore()
sessionStore.init().then(() => {
  app.mount('#app')
})

