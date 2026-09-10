import { createRouter, createWebHistory } from 'vue-router'

import AuthView from '@/components/Auth/Auth.vue'
import TimerView from '@/components/User/Timer.vue'

const routes = [
  {
    path: '/',
    // Send users straight to the main dashboard timer page
    redirect: '/timer'
  },
  {
    path: '/auth',
    name: 'auth',
    component: AuthView
  },
  {
    path: '/timer',
    name: 'timer',
    component: TimerView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router