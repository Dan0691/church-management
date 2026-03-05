<template>
  <div>
    <!-- Welcome Banner -->
    <v-card color="primary" class="mb-6">
      <v-card-text class="d-flex flex-column flex-md-row align-center justify-space-between py-6">
        <div class="text-white">
          <h6 class="text-h4 font-weight-bold mb-2">Welcome back, {{ user?.name || 'Admin' }}!</h6>
          <p class="text-subtitle-1">
            {{ getGreeting() }} • {{ church?.name || 'Church Management System' }}
          </p>
        </div>
        <div class="mt-4 mt-md-0">
          <v-chip color="white" class="text-primary mr-2">
            <v-icon start>mdi-calendar</v-icon>
            {{ currentDate }}
          </v-chip>
          <v-chip color="white" class="text-primary">
            <v-icon start>mdi-clock</v-icon>
            {{ currentTime }}
          </v-chip>
        </div>
      </v-card-text>
    </v-card>

    <!-- Quick Stats -->
    <v-row class="mb-6">
      <v-col cols="6" md="3">
        <v-card class="stats-card" @click="() => router.push('/members')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32" color="white">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ dashboardStats.members.total }}</div>
              <div class="text-caption">Total Members</div>
              <div class="text-caption text-success" v-if="dashboardStats.members.growth_percentage >= 0">
                ↑ {{ dashboardStats.members.growth_percentage.toFixed(1) }}% from last month
              </div>
              <div class="text-caption text-error" v-else>
                ↓ {{ Math.abs(dashboardStats.members.growth_percentage).toFixed(1) }}% from last month
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card class="stats-card" @click="() => router.push('/events')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32" color="white">mdi-calendar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ dashboardStats.events.upcoming }}</div>
              <div class="text-caption">Upcoming Events</div>
              <div class="text-caption">{{ dashboardStats.events.total }} total</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32" color="white">mdi-chart-line</v-icon>
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ dashboardStats.attendance.this_month }}</div>
              <div class="text-caption">This Month Attendance</div>
              <div class="text-caption" :class="dashboardStats.attendance.growth_percentage >= 0 ? 'text-success' : 'text-error'">
                {{ dashboardStats.attendance.growth_percentage >= 0 ? '↑' : '↓' }} {{ Math.abs(dashboardStats.attendance.growth_percentage).toFixed(1) }}%
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card class="stats-card" @click="() => router.push('/members?filter=new')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32" color="white">mdi-account-plus</v-icon>
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ dashboardStats.members.new_this_month }}</div>
              <div class="text-caption">New This Month</div>
              <div class="text-caption">Active: {{ dashboardStats.members.active }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts and Analytics -->
    <v-row class="mb-6">
      <v-col cols="12" lg="8">
        <v-card>
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Attendance Trends</span>
            <v-select
              v-model="trendPeriod"
              :items="trendPeriods"
              density="compact"
              variant="outlined"
              style="max-width: 150px;"
            ></v-select>
          </v-card-title>
          <v-card-text>
            <div class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-chart-line</v-icon>
              <h3 class="text-h6 mb-2">Charts Coming Soon</h3>
              <p class="text-medium-emphasis">Attendance analytics and charts will be available in the next update.</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" lg="4">
        <v-card>
          <v-card-title>Member Distribution</v-card-title>
          <v-card-text>
            <div class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-chart-pie</v-icon>
              <h3 class="text-h6 mb-2">Pie Chart Coming Soon</h3>
              <p class="text-medium-emphasis">Member distribution charts will be available in the next update.</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activity -->
    <v-row>
      <v-col cols="12" lg="6">
        <v-card>
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Recent Members</span>
            <v-btn variant="text" to="/members">View All</v-btn>
          </v-card-title>
          <v-card-text>
            <v-list lines="two" v-if="recentMembers.length > 0">
              <v-list-item
                v-for="member in recentMembers"
                :key="member.id"
                :to="`/members/${member.id}`"
              >
                <template #prepend>
                  <v-avatar :color="getAvatarColor(member)" size="40">
                    <span class="text-white">{{ getInitials(member) }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ member.first_name }} {{ member.last_name }}</v-list-item-title>
                <v-list-item-subtitle>
                  Joined {{ formatRelativeDate(member.join_date) }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
            <div v-else class="text-center py-8">
              <v-icon size="48" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
              <p class="text-medium-emphasis">No recent members</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" lg="6">
        <v-card>
          <v-card-title class="d-flex justify-space-between align-center">
            <span>Upcoming Events</span>
            <v-btn variant="text" to="/events">View All</v-btn>
          </v-card-title>
          <v-card-text>
            <v-list lines="two" v-if="upcomingEvents.length > 0">
              <v-list-item
                v-for="event in upcomingEvents"
                :key="event.id"
                :to="`/events/${event.id}`"
              >
                <template #prepend>
                  <v-avatar :color="getEventColor(event)" size="40">
                    <v-icon color="white">{{ getEventIcon(event) }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ event.title }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ formatDateTime(event.start_date) }} • {{ event.location || 'No location' }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
            <div v-else class="text-center py-8">
              <v-icon size="48" color="grey-lighten-1" class="mb-4">mdi-calendar-remove</v-icon>
              <p class="text-medium-emphasis">No upcoming events</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-card class="mt-6">
      <v-card-title>Quick Actions</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="6" sm="3" v-for="action in quickActions" :key="action.title">
            <v-card
              class="text-center pa-4 quick-action-card"
              :to="action.to"
              @click="action.click"
              hover
            >
              <v-icon size="48" :color="action.color" class="mb-2">{{ action.icon }}</v-icon>
              <div class="text-subtitle-1 font-weight-medium">{{ action.title }}</div>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- System Status -->
    <v-card class="mt-6">
      <v-card-title>System Status</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <div class="d-flex align-center mb-3">
              <v-icon :color="dashboardStats.system.status === 'online' ? 'success' : 'error'" class="mr-2">
                {{ dashboardStats.system.status === 'online' ? 'mdi-check-circle' : 'mdi-alert-circle' }}
              </v-icon>
              <span>System: {{ dashboardStats.system.status === 'online' ? 'Online' : 'Offline' }}</span>
            </div>
          </v-col>
          <v-col cols="12" md="4">
            <div class="d-flex align-center mb-3">
              <v-icon color="info" class="mr-2">mdi-database</v-icon>
              <span>Database: Connected</span>
            </div>
          </v-col>
          <v-col cols="12" md="4">
            <div class="d-flex align-center mb-3">
              <v-icon color="warning" class="mr-2">mdi-cloud</v-icon>
              <span>Last Backup: {{ dashboardStats.system.last_backup }}</span>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

// Reactive data
const currentTime = ref('')
const trendPeriod = ref('month')
const loading = ref(false)
const systemStatus = ref({
  online: true,
  database: true,
  api: true
})
const lastBackup = ref('Yesterday, 2:00 AM')
const recentMembers = ref([])
const upcomingEvents = ref([])
const dashboardStats = ref({
  members: { total: 0, active: 0, new_this_month: 0, growth_percentage: 0 },
  events: { total: 0, upcoming: 0, this_month: 0 },
  attendance: { this_month: 0, last_month: 0, growth_percentage: 0 },
  system: { status: 'online', last_backup: 'N/A', storage_used: '0%' }
})

// Get axios instance with auth token
const getApiClient = () => {
  const token = localStorage.getItem('token')
  const client = axios.create({
    baseURL: '/api',
    headers: {
      'Content-Type': 'application/json'
    }
  })

  if (token) {
    client.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  return client
}

const quickActions = ref([
  {
    title: 'Add Member',
    icon: 'mdi-account-plus',
    color: 'primary',
    to: '/members?action=create'
  },
  {
    title: 'Schedule Event',
    icon: 'mdi-calendar-plus',
    color: 'success',
    to: '/events?action=create'
  },
  {
    title: 'Generate Report',
    icon: 'mdi-file-chart',
    color: 'warning',
    click: () => generateReport()
  },
  {
    title: 'Send Announcement',
    icon: 'mdi-bullhorn',
    color: 'info',
    click: () => sendAnnouncement()
  }
])

const trendPeriods = ref([
  { title: 'Last 7 Days', value: 'week' },
  { title: 'Last Month', value: 'month' },
  { title: 'Last Quarter', value: 'quarter' },
  { title: 'Last Year', value: 'year' }
])

// Computed
const user = computed(() => auth.user)
const church = computed(() => auth.church)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Methods
const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
}

const updateTime = () => {
  currentTime.value = new Date().toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const formatRelativeDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days === 0) return 'today'
  if (days === 1) return 'yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  return `${Math.floor(days / 30)} months ago`
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getInitials = (member) => {
  const first = member.first_name?.[0] || ''
  const last = member.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

const getAvatarColor = (member) => {
  const colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info', 'purple', 'pink', 'teal']
  const name = (member.first_name + member.last_name).toLowerCase()
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const index = Math.abs(hash) % colors.length
  return colors[index]
}

const getEventColor = (eventOrType) => {
  const colors = {
    service: 'primary',
    midweek: 'secondary',
    prayer: 'success',
    bible_study: 'info',
    youth: 'warning',
    children: 'pink',
    women: 'purple',
    men: 'blue',
    outreach: 'teal',
    social: 'orange',
    training: 'indigo',
    conference: 'cyan',
    other: 'grey'
  }
  const type = eventOrType?.type ?? (typeof eventOrType === 'string' ? eventOrType : undefined)
  return colors[type] || 'grey'
}

const getEventIcon = (eventOrType) => {
  const icons = {
    service: 'mdi-church',
    midweek: 'mdi-calendar',
    prayer: 'mdi-hand-heart',
    bible_study: 'mdi-book-open-variant',
    youth: 'mdi-account-group',
    children: 'mdi-human-child',
    women: 'mdi-human-female',
    men: 'mdi-human-male',
    outreach: 'mdi-hand-heart',
    social: 'mdi-party-popper',
    training: 'mdi-school',
    conference: 'mdi-microphone',
    other: 'mdi-calendar'
  }
  const type = eventOrType?.type ?? (typeof eventOrType === 'string' ? eventOrType : undefined)
  return icons[type] || 'mdi-calendar'
}

// Fetch dashboard stats
const fetchDashboardStats = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/dashboard/stats')

    if (response.data.success && response.data.data) {
      dashboardStats.value = response.data.data
      lastBackup.value = response.data.data.system.last_backup || 'N/A'
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    toast.warning('Could not load some dashboard statistics')
  }
}

// Fetch recent members
const fetchRecentMembers = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/members/recent', {
      params: { limit: 5 }
    })

    if (response.data.success && Array.isArray(response.data.data)) {
      recentMembers.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching recent members:', error)
  }
}

// Fetch upcoming events
const fetchUpcomingEvents = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/events/upcoming', {
      params: { limit: 5 }
    })

    if (response.data.success && Array.isArray(response.data.data)) {
      upcomingEvents.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching upcoming events:', error)
  }
}

// Fetch all dashboard data
const fetchDashboardData = async () => {
  loading.value = true
  try {
    // Fetch all data in parallel
    await Promise.all([
      fetchDashboardStats(),
      fetchRecentMembers(),
      fetchUpcomingEvents()
    ])
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

// Quick action functions
const generateReport = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/reports/generate', {
      headers: { Authorization: `Bearer ${token}` },
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `church_report_${new Date().toISOString().split('T')[0]}.pdf`
    a.click()

    toast.success('Report generated successfully')
  } catch (error) {
    console.error('Error generating report:', error)
    toast.info('Report feature coming soon!')
  }
}

const sendAnnouncement = () => {
  toast.info('Announcement feature coming soon!')
}

// Lifecycle
let clockInterval, refreshInterval

onMounted(() => {
  // Start clock
  updateTime()
  clockInterval = setInterval(updateTime, 1000)

  // Fetch data
  fetchDashboardData()

  // Refresh data every 5 minutes
  refreshInterval = setInterval(fetchDashboardData, 5 * 60 * 1000)
})

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval)
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style scoped>
.stats-card {
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.quick-action-card {
  transition: all 0.3s ease;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}

.quick-action-card:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
  transform: scale(1.05);
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .stats-card .v-avatar {
    width: 48px;
    height: 48px;
    min-width: 48px;
  }

  .stats-card .text-h4 {
    font-size: 1.5rem;
  }

  .quick-action-card {
    min-height: 100px;
  }

  .quick-action-card .v-icon {
    font-size: 36px;
  }
}
</style>
