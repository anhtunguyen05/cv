import './assets/main.css'

import { createApp } from 'vue'

import App from './App.vue'
import { pinia } from './app/providers/pinia'
import { queryClient, VueQueryPlugin } from './app/providers/vue-query'
import router from './app/router'
import { useAuthStore } from './features/auth/stores/auth.store'
import { ROUTES } from './shared/constants/routes'

const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(VueQueryPlugin, { queryClient })

window.addEventListener('careerfitcv:auth-expired', () => {
  useAuthStore(pinia).clearAuth()
  void queryClient.cancelQueries().finally(() => queryClient.clear())
  const path = `${window.location.pathname}${window.location.search}`
  if (!window.location.pathname.startsWith(ROUTES.LOGIN)) {
    void router.push({ path: ROUTES.LOGIN, query: { return_to: path } })
  }
})

app.mount('#app')
