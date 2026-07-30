import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import './style.css'

const app = createApp(App)

document.title = import.meta.env.VITE_APP_NAME || document.title

const pinia = createPinia()
app.use(pinia)
app.use(router)

const auth = useAuthStore(pinia)
if (auth.token) auth.fetchCurrentUser()
app.mount('#app')
