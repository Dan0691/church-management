<template>
  <v-app>
    <!-- Enhanced Navigation Drawer with gradient -->
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail"
      permanent
      @click="rail = false"
      :color="rail ? '' : 'background'"
      width="280"
      class="elevation-4"
      :class="{'drawer-rail': rail}"
    >
      <!-- Church/User Info with improved design -->
      <div class="pa-4 gradient-header">
        <div class="d-flex align-center mb-4">
          <v-avatar
            size="64"
            class="mr-3 elevation-3"
            :color="rail ? 'primary' : 'white'"
          >
            <v-icon
              size="32"
              :color="rail ? 'white' : 'primary'"
            >
              mdi-church
            </v-icon>
          </v-avatar>

          <div v-if="!rail" class="flex-grow-1">
            <h3 class="text-h6 font-weight-bold text-white">
              {{ church?.name || 'Church Name' }}
            </h3>
            <div class="d-flex align-center mt-1">
              <v-chip
                size="small"
                color="white"
                class="text-primary font-weight-medium"
              >
                {{ user?.role || 'Administrator' }}
              </v-chip>
              <v-spacer />
              <v-btn
                icon
                size="x-small"
                variant="text"
                color="white"
                @click.stop="rail = !rail"
              >
                <v-icon>mdi-chevron-left</v-icon>
              </v-btn>
            </div>
          </div>

          <v-btn
            v-if="rail"
            icon
            size="small"
            variant="text"
            color="primary"
            @click.stop="rail = !rail"
            class="mt-2"
          >
            <v-icon>mdi-chevron-right</v-icon>
          </v-btn>
        </div>

        <!-- User welcome message (expanded only) -->
        <div v-if="!rail" class="user-welcome mt-4">
          <p class="text-body-2 text-white mb-1">Welcome back,</p>
          <h4 class="text-h5 font-weight-bold text-white">
            {{ user?.name || 'User Name' }}
          </h4>
          <div class="d-flex align-center mt-2">
            <v-icon size="16" color="white" class="mr-1">mdi-email</v-icon>
            <span class="text-caption text-white">{{ user?.email || 'user@church.org' }}</span>
          </div>
        </div>
      </div>

      <v-divider class="my-2" v-if="!rail" />

      <!-- Enhanced Navigation with icons and active states -->
      <v-list density="comfortable" nav class="px-2 navigation-list">
        <v-list-item
          prepend-icon="mdi-view-dashboard-outline"
          :active-icon="activeRoute === '/' ? 'mdi-view-dashboard' : 'mdi-view-dashboard-outline'"
          title="Dashboard"
          value="dashboard"
          to="/"
          rounded="lg"
          :active="activeRoute === '/'"
          class="mb-1"
        >
          <template #prepend>
            <v-icon :color="activeRoute === '/' ? 'primary' : ''">
              {{ activeRoute === '/' ? 'mdi-view-dashboard' : 'mdi-view-dashboard-outline' }}
            </v-icon>
          </template>
        </v-list-item>

        <v-list-item
          prepend-icon="mdi-account-outline"
          :active-icon="activeRoute.includes('members') ? 'mdi-account' : 'mdi-account-outline'"
          title="Members"
          value="members"
          to="/members"
          rounded="lg"
          :active="activeRoute.includes('members')"
          class="mb-1"
        >
          <template #prepend>
            <v-icon :color="activeRoute.includes('members') ? 'primary' : ''">
              {{ activeRoute.includes('members') ? 'mdi-account' : 'mdi-account-outline' }}
            </v-icon>
          </template>
        </v-list-item>

        <v-list-item
          prepend-icon="mdi-calendar-outline"
          :active-icon="activeRoute.includes('events') ? 'mdi-calendar' : 'mdi-calendar-outline'"
          title="Events"
          value="events"
          to="/events"
          rounded="lg"
          :active="activeRoute.includes('events')"
          class="mb-1"
        >
          <template #prepend>
            <v-icon :color="activeRoute.includes('events') ? 'primary' : ''">
              {{ activeRoute.includes('events') ? 'mdi-calendar' : 'mdi-calendar-outline' }}
            </v-icon>
          </template>
        </v-list-item>

        <!-- In NavigationDrawer.vue -->
        <v-list-item
         prepend-icon="mdi-account-group-outline"
          :active-icon="activeRoute.includes('departments') ? 'mdi-account-group' : 'mdi-account-group-outline'"
          title="Departments"
          value="departments"
          to="/departments"
          rounded="lg"
          :active="activeRoute.includes('departments')"
          class="mb-1"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('departments') ? 'primary' : ''">
            {{ activeRoute.includes('departments') ? 'mdi-account-group' : 'mdi-account-group-outline' }}
            </v-icon>
        </template>
        </v-list-item>

        <v-list-item
        prepend-icon="mdi-cash-multiple-outline"
        title="Donations"
        to="/donations"
        :active="activeRoute.includes('donations')"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('donations') ? 'primary' : ''">
                mdi-cash-multiple-outline
            <!-- {{ activeRoute.includes('donations') ? 'mdi-cash-multiple' : 'mdi-cash-multiple-outline' }} -->
            </v-icon>
        </template>
        </v-list-item>

        <v-list-item
        prepend-icon="mdi-pray-outline"
        title="Prayer Requests"
        to="/prayer-requests"
        :active="activeRoute.includes('prayer-requests')"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('prayer-requests') ? 'primary' : ''">
                mdi-pray-outline
            <!-- {{ activeRoute.includes('prayer-requests') ? 'mdi-pray' : 'mdi-pray-outline' }} -->
            </v-icon>
        </template>
        </v-list-item>

        <v-list-item
        prepend-icon="mdi-book-open-variant-outline"
        title="Sermons"
        to="/sermons"
        :active="activeRoute.includes('sermons')"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('sermons') ? 'primary' : ''">
            {{ activeRoute.includes('sermons') ? 'mdi-book-open-variant' : 'mdi-book-open-variant-outline' }}
            </v-icon>
        </template>
        </v-list-item>

        <v-list-item
        prepend-icon="mdi-book-open-variant-outline"
        title="Tasks"
        to="/tasks"
        :active="activeRoute.includes('tasks')"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('tasks') ? 'primary' : ''">
                mdi-book-open-variant-outline
            <!-- {{ activeRoute.includes('tasks') ? 'mdi-book-open-variant' : 'mdi-book-open-variant-outline' }} -->
            </v-icon>
        </template>
        </v-list-item>

         <v-list-item
        prepend-icon="mdi-account-group-outline"
        :active-icon="activeRoute.includes('attendance') ? 'mdi-account-group' : 'mdi-account-group-outline'"
        title="Attendance"
        value="attendance"
        to="/attendance"
        rounded="lg"
        :active="activeRoute.includes('attendance')"
        class="mb-1"
        >
        <template #prepend>
            <v-icon :color="activeRoute.includes('attendance') ? 'primary' : ''">
            {{ activeRoute.includes('attendance') ? 'mdi-account-group' : 'mdi-account-group-outline' }}
            </v-icon>
        </template>
        </v-list-item>

          <v-list-item
            prepend-icon="mdi-chart-box"
            title="Reports"
            to="/reports"
            rounded="lg"
            :active="activeRoute.includes('reports')"
          >
            <template #prepend>
              <v-icon :color="activeRoute.includes('reports') ? 'primary' : ''">
                mdi-chart-box
              </v-icon>
            </template>
          </v-list-item>

        <v-divider class="my-3" v-if="!rail" />

        <!-- Admin Section -->
        <!-- <v-list-group
          v-if="!rail && user?.is_admin"
          value="admin"
          class="mb-1"
        >
          <template #activator="{ props }">
            <v-list-item
              v-bind="props"
              prepend-icon="mdi-shield-account-outline"
              title="Administration"
              rounded="lg"
            ></v-list-item>
          </template>

          <v-list-item
            prepend-icon="mdi-church"
            title="Churches"
            to="/churches"
            rounded="lg"
            :active="activeRoute.includes('churches')"
          >
            <template #prepend>
              <v-icon :color="activeRoute.includes('churches') ? 'primary' : ''">
                mdi-church
              </v-icon>
            </template>
          </v-list-item>

          <v-list-item
            prepend-icon="mdi-account-cog"
            title="Users"
            to="/users"
            rounded="lg"
            :active="activeRoute.includes('users')"
          >
            <template #prepend>
              <v-icon :color="activeRoute.includes('users') ? 'primary' : ''">
                mdi-account-cog
              </v-icon>
            </template>
          </v-list-item>

          <v-list-item
            prepend-icon="mdi-chart-box"
            title="Reports"
            to="/reports"
            rounded="lg"
            :active="activeRoute.includes('reports')"
          >
            <template #prepend>
              <v-icon :color="activeRoute.includes('reports') ? 'primary' : ''">
                mdi-chart-box
              </v-icon>
            </template>
          </v-list-item>
        </v-list-group> -->

        <!-- Collapsed admin icon -->
        <v-list-item
          v-if="rail && user?.is_admin"
          prepend-icon="mdi-shield-account"
          title="Admin"
          rounded="lg"
          class="mb-1"
        >
          <v-tooltip location="right">
            <template #activator="{ props }">
              <div v-bind="props">
                <v-icon>mdi-shield-account</v-icon>
              </div>
            </template>
            <span>Administration</span>
          </v-tooltip>
        </v-list-item>
      </v-list>

      <!-- Bottom Settings & Logout -->
      <template #append>
        <div class="pa-2">
          <v-list density="comfortable" nav>
            <v-list-item
              prepend-icon="mdi-cog-outline"
              title="Settings"
              to="/settings"
              rounded="lg"
              :active="activeRoute.includes('settings')"
              class="mb-1"
            >
              <template #prepend>
                <v-icon :color="activeRoute.includes('settings') ? 'primary' : ''">
                  mdi-cog-outline
                </v-icon>
              </template>
            </v-list-item>

            <v-divider class="my-2" v-if="!rail" />

            <v-list-item
              prepend-icon="mdi-logout"
              title="Logout"
              @click="handleLogout"
              rounded="lg"
              class="logout-item"
            >
              <template #prepend>
                <v-icon color="error">mdi-logout</v-icon>
              </template>
            </v-list-item>
          </v-list>
        </div>
      </template>
    </v-navigation-drawer>

    <!-- Enhanced App Bar with gradient -->
    <v-app-bar color="primary" class="elevation-2">
      <v-app-bar-nav-icon @click="drawer = !drawer" class="hidden-md-and-up" />

        <v-app-bar-title class="header-title">
            <div class="d-flex flex-column flex-md-row align-start align-md-center">
                <div class="d-flex align-center">
                <v-icon class="mr-2 hidden-sm-and-down" size="20">mdi-church</v-icon>

                <h2 class="text-subtitle-1 text-md-h6 font-weight-bold text-white mb-0">
                    {{ church?.name || 'Church Management System' }}
                </h2>
                </div>

                <v-chip
                size="x-small"
                color="white"
                class="mt-1 mt-md-0 ml-md-3 text-primary font-weight-medium"
                >
                {{ currentDate }}
                </v-chip>
            </div>
        </v-app-bar-title>


      <template #append>
        <!-- Quick Stats (Desktop only) -->
        <div class="hidden-sm-and-down d-flex align-center mr-4">
          <div class="d-flex align-center mr-4 quick-stat">
            <v-icon size="20" color="white" class="mr-1">mdi-account-group</v-icon>
            <span class="text-body-2 text-white">{{ memberCount || 0 }}</span>
          </div>
          <div class="d-flex align-center mr-4 quick-stat">
            <v-icon size="20" color="white" class="mr-1">mdi-calendar</v-icon>
            <span class="text-body-2 text-white">{{ upcomingEventCount || 0 }}</span>
          </div>
        </div>

        <!-- Enhanced Notification -->
        <v-menu location="bottom end" offset="10">
          <template #activator="{ props }">
            <v-btn icon v-bind="props" class="mr-2">
              <v-badge :content="unreadNotifications" :color="unreadNotifications > 0 ? 'error' : 'transparent'" dot>
                <v-icon color="white">mdi-bell</v-icon>
              </v-badge>
            </v-btn>
          </template>
          <v-card width="350">
            <v-card-title class="d-flex justify-space-between align-center">
              <span class="text-h6">Notifications</span>
              <v-btn size="small" variant="text" @click="markAllAsRead">
                Mark all as read
              </v-btn>
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list lines="two" max-height="300">
                <v-list-item v-for="notification in notifications" :key="notification.id">
                  <template #prepend>
                    <v-avatar :color="notification.type === 'event' ? 'green' : 'blue'" size="40">
                      <v-icon color="white">
                        {{ notification.type === 'event' ? 'mdi-calendar' : 'mdi-account' }}
                      </v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ notification.title }}</v-list-item-title>
                  <v-list-item-subtitle>{{ notification.time }}</v-list-item-subtitle>
                </v-list-item>
                <div v-if="notifications.length === 0" class="text-center py-4">
                  <v-icon size="48" color="grey">mdi-bell-off</v-icon>
                  <p class="text-caption mt-2">No notifications</p>
                </div>
              </v-list>
            </v-card-text>
          </v-card>
        </v-menu>

        <!-- Enhanced User Menu -->
        <v-menu location="bottom end" offset="10">
          <template #activator="{ props }">
            <div class="user-menu-activator" v-bind="props">
              <v-avatar size="40" color="white" class="elevation-2">
                <span class="text-primary text-h6 font-weight-bold">
                  {{ getUserInitials() }}
                </span>
              </v-avatar>
              <div class="user-info ml-2 hidden-sm-and-down">
                <div class="text-body-2 font-weight-medium text-white">{{ user?.name }}</div>
                <div class="text-caption text-white">{{ user?.role || 'Administrator' }}</div>
              </div>
              <v-icon color="white" class="ml-1">mdi-chevron-down</v-icon>
            </div>
          </template>
          <v-card width="250">
            <v-card-text class="pa-4">
              <div class="d-flex align-center mb-4">
                <v-avatar size="56" color="primary" class="mr-3">
                  <span class="text-white text-h5 font-weight-bold">
                    {{ getUserInitials() }}
                  </span>
                </v-avatar>
                <div>
                  <h4 class="text-h6 font-weight-bold">{{ user?.name }}</h4>
                  <p class="text-caption text-medium-emphasis">{{ user?.email }}</p>
                  <v-chip size="small" color="primary" class="mt-1">
                    {{ user?.role || 'Administrator' }}
                  </v-chip>
                </div>
              </div>
              <v-divider />
            </v-card-text>
            <v-list density="compact">
              <v-list-item prepend-icon="mdi-account" title="My Profile" @click="goToProfile" />
              <v-list-item prepend-icon="mdi-cog" title="Settings" @click="goTo('settings')" />
              <v-list-item prepend-icon="mdi-shield" title="Admin Panel" @click="goTo('admin')" v-if="user?.is_admin" />
              <v-divider />
              <v-list-item
                prepend-icon="mdi-logout"
                title="Logout"
                @click="handleLogout"
                color="error"
              />
            </v-list>
          </v-card>
        </v-menu>
      </template>
    </v-app-bar>

    <!-- Main Content Area with subtle background pattern -->
    <v-main style="background-color:#f8fafc; background-image: radial-gradient(#e2e8f0 1px, transparent 1px); background-size: 20px 20px;">
      <v-container fluid class="pa-4 pa-md-6">
        <v-fade-transition mode="out-in">
          <router-view />
        </v-fade-transition>
      </v-container>
    </v-main>

    <!-- Enhanced Footer -->
  <v-footer app height="auto" class="bg-grey-lighten-5 py-1 elevation-2 footer-compact">
    <v-container class="py-0">
      <div class="d-flex flex-column flex-sm-row justify-space-between align-center">
        <div class="d-flex align-center mb-2 mb-sm-0">
          <v-icon color="primary" size="10" class="mr-2">mdi-church</v-icon>
          <span class="text-caption text-medium-emphasis">
            © {{ new Date().getFullYear() }} {{ church?.name || 'Church Management System' }}
          </span>
          <v-chip
            size="x-small"
            :color="systemStatus === 'online' ? 'success' : 'error'"
            class="ml-2"
            variant="tonal"
          >
            {{ systemStatus }}
          </v-chip>
        </div>

        <div class="d-flex align-center flex-wrap justify-center text-center">
          <v-chip size="x-small" color="primary" variant="outlined" class="mr-2 mb-1">
            v{{ appVersion }}
          </v-chip>

          <span class="text-caption text-medium-emphasis footer-text">
            Last updated: {{ lastUpdateTime }}
          </span>
        </div>

        <div class="d-flex align-center mt-2 mt-sm-0">
          <v-tooltip text="Refresh">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon
                size="x-small"
                variant="text"
                color="grey"
                @click="refreshPage"
              >
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </template>
          </v-tooltip>

          <v-tooltip text="Toggle Dark Mode">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon
                size="x-small"
                variant="text"
                color="grey"
                @click="toggleDarkMode"
                class="ml-1"
              >
                <v-icon>mdi-theme-light-dark</v-icon>
              </v-btn>
            </template>
          </v-tooltip>
        </div>
      </div>
    </v-container>
  </v-footer>


    <!-- Global Loading Indicator -->
    <v-overlay
      v-model="globalLoading"
      class="align-center justify-center"
      persistent
    >
      <v-progress-circular
        indeterminate
        size="64"
        color="primary"
      ></v-progress-circular>
    </v-overlay>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import axios from 'axios'
import { useDisplay } from 'vuetify'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { mdAndUp } = useDisplay()

const drawer = ref(true)
const rail = ref(false)
const globalLoading = ref(false)
const memberCount = ref(0)
const upcomingEventCount = ref(0)
const notifications = ref([])

// Current route for active state highlighting
const activeRoute = computed(() => route.path)

// User and church data
const user = computed(() => auth.user)
const church = computed(() => auth.church)

// Current date formatted
const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// App version
const appVersion = computed(() => '1.2.0')

// Last update time
// const lastUpdateTime = computed(() => {
//   const now = new Date()
//   return now.toLocaleTimeString('en-US', {
//     hour: '2-digit',
//     minute: '2-digit'
//   })
// })

// Unread notifications count
const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

// Initialize
onMounted(async () => {
  if (!auth.user) {
    globalLoading.value = true
    await auth.fetchUser()
    globalLoading.value = false
  }

  // Auto-close drawer on mobile
  if (!mdAndUp.value) {
    drawer.value = false
  }

  // Load quick stats
  fetchQuickStats()
  fetchNotifications()
})

// Watch for route changes
watch(() => route.path, () => {
  // Auto-close drawer on mobile when navigating
  if (!mdAndUp.value) {
    drawer.value = false
  }
})

// Fetch quick stats
const fetchQuickStats = async () => {
  try {
    const token = localStorage.getItem('token')

    // Fetch member count
    const membersRes = await axios.get('/api/members', {
      headers: { Authorization: `Bearer ${token}` }
    })

    let members = []
    if (membersRes.data && Array.isArray(membersRes.data.data)) {
      members = membersRes.data.data
    } else if (Array.isArray(membersRes.data)) {
      members = membersRes.data
    }

    memberCount.value = members.length

    // Fetch event count
    const eventsRes = await axios.get('/api/events', {
      headers: { Authorization: `Bearer ${token}` }
    })

    let events = []
    if (eventsRes.data && Array.isArray(eventsRes.data.data)) {
      events = eventsRes.data.data
    } else if (Array.isArray(eventsRes.data)) {
      events = eventsRes.data
    }

    const now = new Date()
    upcomingEventCount.value = events.filter(e => new Date(e.start_date) >= now).length

  } catch (error) {
    console.error('Error fetching quick stats:', error)
  }
}

// Fetch notifications
const fetchNotifications = async () => {
  try {
    // Mock notifications - replace with real API call
    notifications.value = [
      {
        id: 1,
        title: 'New Member Registered',
        description: 'John Doe joined the church',
        time: '10 min ago',
        type: 'member',
        read: false
      },
      {
        id: 2,
        title: 'Upcoming Event',
        description: 'Sunday Service starts in 2 hours',
        time: '1 hour ago',
        type: 'event',
        read: true
      },
      {
        id: 3,
        title: 'Attendance Record',
        description: 'Monthly attendance report is ready',
        time: '3 hours ago',
        type: 'report',
        read: false
      }
    ]
  } catch (error) {
    console.error('Error fetching notifications:', error)
  }
}

// Helper functions
const getUserInitials = () => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Navigation functions
const goTo = (name) => {
  router.push({ name })
}

const goToProfile = () => {
  router.push({ name: 'profile' })
}

// Notification functions
const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
}

// UI functions
const refreshPage = () => {
  window.location.reload()
}

// const toggleDarkMode = () => {
//   // Implement dark mode toggle
//   console.log('Toggle dark mode')
// }

// Logout function
const handleLogout = async () => {
  globalLoading.value = true
  await auth.logout()
  globalLoading.value = false
  router.push({ name: 'login' })
}
////////////////////////////////////////////////////////////////
// Add these imports
// import { ref, computed, onMounted, watch } from 'vue'
// import { useRouter, useRoute } from 'vue-router'
// import { useAuthStore } from '../../stores/auth'
// import { useDisplay } from 'vuetify'
// import axios from 'axios'

// Add theme control
import { useTheme } from 'vuetify'
const theme = useTheme()

// Add dark mode toggle function
const toggleDarkMode = () => {
  theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark'
  localStorage.setItem('darkMode', theme.global.name.value)
}

// Load dark mode preference on mount
onMounted(() => {
  const savedTheme = localStorage.getItem('darkMode')
  if (savedTheme) {
    theme.global.name.value = savedTheme
  }
})

// Add real-time updates
const lastUpdateTime = computed(() => {
  const now = new Date()
  return now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
})

// Add system status indicator
const systemStatus = ref('online')
const checkSystemStatus = async () => {
  try {
    await axios.get('/api/health')
    systemStatus.value = 'online'
  } catch {
    systemStatus.value = 'offline'
  }
}

// Check status periodically
onMounted(() => {
  checkSystemStatus()
  setInterval(checkSystemStatus, 60000) // Check every minute
})

// Add user activity tracking
let idleTimer
const resetIdleTimer = () => {
  clearTimeout(idleTimer)
  idleTimer = setTimeout(() => {
    // Show idle notification or auto-logout
    console.log('User idle for 15 minutes')
  }, 15 * 60 * 1000) // 15 minutes
}

onMounted(() => {
  // Reset timer on user activity
  document.addEventListener('mousemove', resetIdleTimer)
  document.addEventListener('keypress', resetIdleTimer)
  resetIdleTimer()
})

</script>

<style scoped>
/* Enhanced Navigation Drawer */
.gradient-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 0 0 16px 16px;
  margin-bottom: 8px;
}

.user-welcome {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.navigation-list .v-list-item--active {
  background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-left: 4px solid #667eea;
}

.navigation-list .v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
  transform: translateX(4px);
  transition: all 0.2s ease;
}

.logout-item:hover {
  background-color: rgba(244, 67, 54, 0.1);
  color: #f44336;
}

.drawer-rail .v-list-item {
  justify-content: center;
  padding: 0;
  min-height: 56px;
}

/* Enhanced App Bar */
.v-app-bar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.user-menu-activator {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 4px;
  border-radius: 24px;
  transition: all 0.2s ease;
}

.user-menu-activator:hover {
  background: rgba(255, 255, 255, 0.1);
}

.quick-stat {
  padding: 4px 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  backdrop-filter: blur(10px);
}

/* Enhanced Footer */
.v-footer {
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* Smooth transitions */
.v-list-item {
  transition: all 0.3s ease;
}

.v-avatar {
  transition: transform 0.3s ease;
}

.v-avatar:hover {
  transform: scale(1.05);
}

/* Add smooth transitions for dark mode */
.v-application {
  transition: background-color 0.3s ease;
}

/* Enhance card shadows */
.v-card {
  transition: box-shadow 0.3s ease;
}

.v-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Improve table hover effects */
.v-data-table tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.04) !important;
}

</style>
