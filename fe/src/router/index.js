import { createRouter, createWebHistory } from 'vue-router'

import AuthView from '@/components/Auth/Auth.vue'
import TimerView from '@/components/User/Timer.vue'
import { getCurrentUser } from '@/services/api'

const routes = [
  {
    path: '/',
    redirect: { name: 'authdefault' }
  },
  {
    path: '/authdefault',
    name: 'authdefault',
    component: AuthView
  },
  {
    path: '/timer',
    name: 'timer',
    component: TimerView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  const token = localStorage.getItem('panodoro.authToken')

  if (!token) {
    return to.meta.requiresAuth ? { name: 'authdefault' } : true
  }

  try {
    await getCurrentUser()

    if (to.name === 'authdefault') {
      return { name: 'timer' }
    }

    return true
  } catch {
    localStorage.removeItem('panodoro.authToken')
    localStorage.removeItem('panodoro.activeUser')

    return to.meta.requiresAuth ? { name: 'authdefault' } : true
  }
})

export default router