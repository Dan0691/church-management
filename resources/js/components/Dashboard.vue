<template>
  <div>
    <!-- Welcome Header -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card color="primary" variant="flat" rounded="lg">
          <v-card-text class="pa-6">
            <div class="d-flex justify-space-between align-center">
              <div>
                <h1 class="text-h4 font-weight-bold text-white">Welcome back, {{ user?.name }}!</h1>
                <p class="text-subtitle-1 text-white mt-2">
                  Here's what's happening with your church today.
                </p>
              </div>
              <v-btn color="white" variant="tonal" rounded="lg">
                <v-icon start icon="mdi-plus"></v-icon>
                Quick Action
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col v-for="stat in stats" :key="stat.title" cols="12" sm="6" md="3">
        <v-card elevation="2" rounded="lg" height="140">
          <v-card-text class="pa-4">
            <div class="d-flex justify-space-between align-center">
              <div>
                <p class="text-caption text-medium-emphasis mb-1">{{ stat.title }}</p>
                <h2 class="text-h4 font-weight-bold">{{ stat.value }}</h2>
                <v-chip :color="stat.trendColor" size="small" class="mt-2">
                  <v-icon start :icon="stat.trendIcon"></v-icon>
                  {{ stat.trend }}
                </v-chip>
              </div>
              <v-avatar :color="stat.color" size="56">
                <v-icon size="28" color="white">{{ stat.icon }}</v-icon>
              </v-avatar>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Members & Upcoming Events -->
    <v-row>
      <!-- Recent Members -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex justify-space-between align-center">
            <span class="text-h6">Recent Members</span>
            <v-btn variant="text" color="primary" :to="{ name: 'members' }">
              View All
              <v-icon end icon="mdi-chevron-right"></v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text>
            <v-list lines="two">
              <v-list-item
                v-for="member in recentMembers"
                :key="member.id"
                :prepend-avatar="member.avatar"
              >
                <template #title>
                  {{ member.first_name }} {{ member.last_name }}
                </template>
                <template #subtitle>
                  Joined {{ member.join_date }}
                </template>
                <template #append>
                  <v-chip :color="getStatusColor(member.membership_status)" size="small">
                    {{ member.membership_status }}
                  </v-chip>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Upcoming Events -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex justify-space-between align-center">
            <span class="text-h6">Upcoming Events</span>
            <v-btn variant="text" color="primary" :to="{ name: 'events' }">
              View All
              <v-icon end icon="mdi-chevron-right"></v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text>
            <div class="event-list">
              <div v-for="event in upcomingEvents" :key="event.id" class="event-item mb-3">
                <div class="d-flex align-center">
                  <v-avatar :color="getEventColor(event.type)" size="40" class="mr-3">
                    <v-icon color="white">{{ getEventIcon(event.type) }}</v-icon>
                  </v-avatar>
                  <div class="flex-grow-1">
                    <div class="font-weight-medium">{{ event.title }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ event.date }} • {{ event.location }}
                    </div>
                  </div>
                  <v-chip size="small" :color="getEventColor(event.type)" variant="flat">
                    {{ event.type }}
                  </v-chip>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mt-6">
      <v-col cols="12">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="text-h6">Quick Actions</v-card-title>
          <v-card-text>
            <div class="d-flex flex-wrap gap-3">
              <v-btn
                v-for="action in quickActions"
                :key="action.title"
                :color="action.color"
                variant="tonal"
                :prepend-icon="action.icon"
                rounded="lg"
                size="large"
                @click="handleQuickAction(action)"
              >
                {{ action.title }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const user = ref({
  name: 'Church Administrator',
  role: 'admin'
})

const stats = ref([
  { title: 'Total Members', value: '156', icon: 'mdi-account-group', color: 'primary', trend: '+12%', trendIcon: 'mdi-arrow-up', trendColor: 'success' },
  { title: 'Active Volunteers', value: '24', icon: 'mdi-hand-heart', color: 'secondary', trend: '+5%', trendIcon: 'mdi-arrow-up', trendColor: 'success' },
  { title: 'Upcoming Events', value: '8', icon: 'mdi-calendar', color: 'info', trend: '2 new', trendIcon: 'mdi-plus', trendColor: 'info' },
  { title: 'Monthly Giving', value: '$4,250', icon: 'mdi-cash', color: 'warning', trend: '+18%', trendIcon: 'mdi-arrow-up', trendColor: 'success' }
])

const recentMembers = ref([
  { id: 1, first_name: 'John', last_name: 'Doe', join_date: 'Jan 15, 2024', membership_status: 'active', avatar: 'https://cdn.vuetifyjs.com/images/lists/1.jpg' },
  { id: 2, first_name: 'Jane', last_name: 'Smith', join_date: 'Jan 10, 2024', membership_status: 'active', avatar: 'https://cdn.vuetifyjs.com/images/lists/2.jpg' },
  { id: 3, first_name: 'Robert', last_name: 'Johnson', join_date: 'Jan 5, 2024', membership_status: 'visitor', avatar: 'https://cdn.vuetifyjs.com/images/lists/3.jpg' }
])

const upcomingEvents = ref([
  { id: 1, title: 'Sunday Service', type: 'service', date: 'Jan 21, 10:00 AM', location: 'Main Sanctuary' },
  { id: 2, title: 'Youth Group Meeting', type: 'meeting', date: 'Jan 19, 6:00 PM', location: 'Youth Center' },
  { id: 3, title: 'Community Outreach', type: 'outreach', date: 'Jan 25, 9:00 AM', location: 'Downtown Square' }
])

const quickActions = [
  { title: 'Add Member', icon: 'mdi-account-plus', color: 'primary', action: 'addMember' },
  { title: 'Create Event', icon: 'mdi-calendar-plus', color: 'success', action: 'addEvent' },
  { title: 'Record Donation', icon: 'mdi-cash-plus', color: 'warning', action: 'addDonation' },
  { title: 'Send Announcement', icon: 'mdi-bullhorn', color: 'info', action: 'sendAnnouncement' }
]

const getStatusColor = (status) => {
  const colors = { active: 'success', inactive: 'error', visitor: 'warning' }
  return colors[status] || 'default'
}

const getEventColor = (type) => {
  const colors = { service: 'primary', meeting: 'secondary', outreach: 'success', social: 'warning' }
  return colors[type] || 'default'
}

const getEventIcon = (type) => {
  const icons = { service: 'mdi-church', meeting: 'mdi-account-group', outreach: 'mdi-hand-heart', social: 'mdi-party-popper' }
  return icons[type] || 'mdi-calendar'
}

const handleQuickAction = (action) => {
  switch(action.action) {
    case 'addMember':
      router.push({ name: 'members' })
      break
    case 'addEvent':
      router.push({ name: 'events' })
      break
    default:
      console.log('Action:', action.action)
  }
}

onMounted(() => {
  console.log('Dashboard loaded')
})
</script>

<style scoped>
.event-list {
  max-height: 300px;
  overflow-y: auto;
}

.event-item {
  padding: 8px;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.event-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>
