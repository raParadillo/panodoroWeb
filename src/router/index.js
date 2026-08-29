import { createRouter, createWebHistory } from 'vue-router'

import AuthView from '@/components/Auth/Auth.vue'
import TimerView from '@/components/User/Timer.vue'


const routes = [
   {
    path: '/',
    // This forces the root path to drop the user straight off onto the /todo component
    redirect: '/todo'
  },
  {
    path: '/',
    name: 'auth',
    component: AuthView
  },
  {
    path: '/timer',
    name: 'timer',
    component: TimerView
  },
  {
    path: '/todo',
    name: 'todo',
    component: () => import('@/components/User/ToDo.vue'),
    meta: { requiresAuth: true }  // Locked down
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router