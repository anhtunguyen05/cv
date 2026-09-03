import './assets/main.css'

import { createApp } from 'vue'

import App from './App.vue'
import { pinia } from './app/providers/pinia'
import { queryClient, VueQueryPlugin } from './app/providers/vue-query'
import router from './app/router'

const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(VueQueryPlugin, { queryClient })

app.mount('#app')
