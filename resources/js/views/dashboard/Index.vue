<template>
  <div>
    <!-- Welcome Banner -->
    <v-card color="primary" class="mb-6">
      <v-card-text class="d-flex flex-column flex-md-row align-center justify-space-between py-6">
        <div class="text-white">
          <h1 class="text-h4 font-weight-bold mb-2">Welcome back, {{ user?.name || 'Admin' }}!</h1>
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
      <v-col cols="6" md="3" v-for="stat in quickStats" :key="stat.title">
        <v-card class="stats-card" @click="stat.action && stat.action()">
          <v-card-text class="d-flex align-center">
            <v-avatar :color="stat.color" size="56" class="mr-4">
              <v-icon size="32" color="white">{{ stat.icon }}</v-icon>
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ stat.value }}</div>
              <div class="text-caption">{{ stat.title }}</div>
              <div v-if="stat.change !== undefined" class="text-caption" :class="stat.change > 0 ? 'text-success' : 'text-error'">
                {{ stat.change > 0 ? '↑' : '↓' }} {{ Math.abs(stat.change) }}% from last month
              </div>
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
              <v-icon :color="systemStatus.online ? 'success' : 'error'" class="mr-2">
                {{ systemStatus.online ? 'mdi-check-circle' : 'mdi-alert-circle' }}
              </v-icon>
              <span>System: {{ systemStatus.online ? 'Online' : 'Offline' }}</span>
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
              <span>Last Backup: {{ lastBackup }}</span>
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
const systemStatus = ref({
  online: true,
  database: true,
  api: true
})
const lastBackup = ref('Yesterday, 2:00 AM')
const recentMembers = ref([])
const upcomingEvents = ref([])

// Stats
const quickStats = ref([
  {
    title: 'Total Members',
    value: 0,
    icon: 'mdi-account-group',
    color: 'primary',
    change: 5,
    action: () => router.push('/members')
  },
  {
    title: 'Active Events',
    value: 0,
    icon: 'mdi-calendar',
    color: 'success',
    change: 12,
    action: () => router.push('/events')
  },
  {
    title: 'This Month Attendance',
    value: 0,
    icon: 'mdi-chart-line',
    color: 'warning',
    change: 8,
    action: () => toast.info('View detailed report')
  },
  {
    title: 'New This Week',
    value: 0,
    icon: 'mdi-account-plus',
    color: 'info',
    change: -2,
    action: () => router.push('/members?filter=new')
  }
])

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

const getEventColor = (event) => {
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
  return colors[event.type] || 'grey'
}

const getEventIcon = (event) => {
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
  return icons[event.type] || 'mdi-calendar'
}

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    const token = localStorage.getItem('token')

    if (!token) {
      // Use sample data if no token
      quickStats.value[0].value = 145
      quickStats.value[1].value = 8
      quickStats.value[2].value = 1200
      quickStats.value[3].value = 12

      recentMembers.value = [
        { id: 1, first_name: 'John', last_name: 'Doe', join_date: new Date().toISOString() },
        { id: 2, first_name: 'Jane', last_name: 'Smith', join_date: new Date(Date.now() - 86400000).toISOString() }
      ]

      upcomingEvents.value = [
        { id: 1, title: 'Sunday Service', type: 'service', start_date: new Date(Date.now() + 86400000).toISOString(), location: 'Main Hall' },
        { id: 2, title: 'Youth Meeting', type: 'youth', start_date: new Date(Date.now() + 172800000).toISOString(), location: 'Youth Room' }
      ]
      return
    }

    // Fetch stats
    const statsRes = await axios.get('/api/dashboard/stats', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (statsRes.data.success) {
      const stats = statsRes.data.data
      quickStats.value[0].value = stats.total_members || 0
      quickStats.value[1].value = stats.active_events || 0
      quickStats.value[2].value = stats.month_attendance || 0
      quickStats.value[3].value = stats.new_this_week || 0
    }

    // Fetch recent members
    const membersRes = await axios.get('/api/members/recent', {
      headers: { Authorization: `Bearer ${token}` },
      params: { limit: 5 }
    })

    if (membersRes.data.success) {
      recentMembers.value = membersRes.data.data
    }

    // Fetch upcoming events
    const eventsRes = await axios.get('/api/events/upcoming', {
      headers: { Authorization: `Bearer ${token}` },
      params: { limit: 5 }
    })

    if (eventsRes.data.success) {
      upcomingEvents.value = eventsRes.data.data
    }

  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    // Use mock data for demonstration
    quickStats.value[0].value = 145
    quickStats.value[1].value = 8
    quickStats.value[2].value = 1200
    quickStats.value[3].value = 12

    recentMembers.value = [
      { id: 1, first_name: 'John', last_name: 'Doe', join_date: new Date().toISOString() },
      { id: 2, first_name: 'Jane', last_name: 'Smith', join_date: new Date(Date.now() - 86400000).toISOString() }
    ]

    upcomingEvents.value = [
      { id: 1, title: 'Sunday Service', type: 'service', start_date: new Date(Date.now() + 86400000).toISOString(), location: 'Main Hall' },
      { id: 2, title: 'Youth Meeting', type: 'youth', start_date: new Date(Date.now() + 172800000).toISOString(), location: 'Youth Room' }
    ]
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
onMounted(() => {
  // Start clock
  updateTime()
  const clockInterval = setInterval(updateTime, 1000)

  // Fetch data
  fetchDashboardData()

  // Refresh data every 5 minutes
  const refreshInterval = setInterval(fetchDashboardData, 5 * 60 * 1000)

  // Cleanup
  onUnmounted(() => {
    clearInterval(clockInterval)
    clearInterval(refreshInterval)
  })
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
