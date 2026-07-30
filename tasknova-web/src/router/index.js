import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', component: () => import('../views/SplashScreen.vue') },
  { path: '/login', component: () => import('../views/LoginScreen.vue') },
  { path: '/register', component: () => import('../views/RegisterScreen.vue') },
  { path: '/forgot-password', component: () => import('../views/ForgotPasswordScreen.vue') },
  { path: '/complete-profile', name: 'complete-profile', component: () => import('../views/ProfileScreen.vue'), meta: { requiresAuth: true } },
  { path: '/dashboard', component: () => import('../views/DashboardScreen.vue'), meta: { requiresAuth: true } },
  { path: '/tasks', component: () => import('../views/TasksScreen.vue'), meta: { requiresAuth: true } },
  { path: '/tasks/new', component: () => import('../views/TaskFormScreen.vue'), meta: { requiresAuth: true } },
  { path: '/tasks/:id', component: () => import('../views/TaskDetailScreen.vue'), meta: { requiresAuth: true } },
  { path: '/tasks/:id/edit', component: () => import('../views/TaskFormScreen.vue'), meta: { requiresAuth: true } },
  { path: '/categories', component: () => import('../views/CategoriesScreen.vue'), meta: { requiresAuth: true } },
  { path: '/calendar', component: () => import('../views/CalendarScreen.vue'), meta: { requiresAuth: true } },
  { path: '/statistics', component: () => import('../views/StatisticsScreen.vue'), meta: { requiresAuth: true } },
  { path: '/profile', component: () => import('../views/ProfileScreen.vue'), meta: { requiresAuth: true } },
  { path: '/offline', component: () => import('../views/ErrorScreen.vue'), props: { code: 'offline' } },
  { path: '/error', component: () => import('../views/ErrorScreen.vue'), props: { code: '500' } },
  { path: '/:pathMatch(.*)*', component: () => import('../views/ErrorScreen.vue'), props: { code: '404' } },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  const token = localStorage.getItem('tasknova_token') || sessionStorage.getItem('tasknova_token')
  if (to.meta.requiresAuth && !token) return { path: '/login', query: { redirect: to.fullPath } }
  if (['/login', '/register', '/forgot-password'].includes(to.path) && token) return '/dashboard'
  return true
})

export default router
