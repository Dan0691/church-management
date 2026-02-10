import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Import layout and views
import AppLayout from '../components/layout/AppLayout.vue'
import Login from '../views/auth/login.vue'
import Register from '../views/auth/Register.vue' // Add this import
import Profile from '../views/profile/index.vue'

// We'll create these view files in the next step
const Dashboard = () => import('../views/dashboard/Index.vue')
const Members = () => import('../views/members/index.vue')
const Events = () => import('../views/events/index.vue')
const Departments = () => import('../views/departments/index.vue')
const Donations = () => import('../views/donations/index.vue')
const PrayerRequests = () => import('../views/prayer-requests/index.vue')
const Sermons = () => import('../views/sermons/index.vue')
const Tasks = () => import('../views/tasks/index.vue')
const Churches = () => import('../views/churches/index.vue')
const Settings = () => import('../views/settings/index.vue')
const Reports = () => import('../views/reports/index.vue')
const Attendance = () => import('../views/attendance/index.vue')


const routes = [
  {
    path: '/',
    name: 'login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/register', // Add this route
    name: 'register',
    component: Register,
    meta: { guest: true }
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' }
      },
      {
        path: 'profile',
        name: 'profile',
        component: Profile,
        meta: {
            requiresAuth: true,
            title: 'My Profile'}
      },
      {
        path: 'members',
        name: 'members',
        component: Members,
        meta: { title: 'Members' }
      },
      {
        path: 'events',
        name: 'events',
        component: Events,
        meta: { title: 'Events' }
      },
      {
        path: 'departments',
        name: 'departments',
        component: () => import('../views/departments/index.vue')
      },
      {
        path: 'donations',
        name: 'donations',
        component: () => import('../views/donations/index.vue')
      },
      {
        path: 'prayer-requests',
        name: 'prayer-requests',
        component: () => import('../views/prayer-requests/index.vue')
      },
      {
        path: 'sermons',
        name: 'sermons',
        component: () => import('../views/sermons/index.vue')
      },
      {
        path: 'tasks',
        name: 'tasks',
        component: () => import('../views/tasks/index.vue')
      },
      {
        path: 'attendance',
        name: 'attendance',
        component: () => import('../views/attendance/index.vue')
      },
      {
        path: 'reports',
        name: 'reports',
        component: () => import('../views/reports/index.vue')
      },
      {
        path: 'churches',
        name: 'churches',
        component: Churches,
        meta: { title: 'Churches' }
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
        meta: { title: 'Settings' }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  }
  // Check if route is only for guests (like login and register)
  else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  }
  // Otherwise, proceed
  else {
    next()
  }
})

// Update page title based on route meta
router.beforeEach((to, from, next) => {
  const title = to.meta.title || 'Church Management System'
  document.title = title
  next()
})

export default router
