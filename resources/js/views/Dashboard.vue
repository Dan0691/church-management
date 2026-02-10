<template>
  <div>
    <!-- Welcome Section -->
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold mb-2">Welcome back, {{ auth.user?.name }}!</h1>
      <p class="text-body-1 text-medium-emphasis">
        Here's what's happening with {{ auth.church?.name || 'your church' }} today.
      </p>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card>
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.totalMembers || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Total Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card>
          <v-card-text class="d-flex align-center">
            <v-avatar color="secondary" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.upcomingEvents || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Upcoming Events</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card>
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-cash</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">${{ formatCurrency(stats.totalDonations || 0) }}</div>
              <div class="text-caption text-medium-emphasis">Total Donations</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card>
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-line</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.attendanceRate || 0 }}%</div>
              <div class="text-caption text-medium-emphasis">Attendance Rate</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activity & Quick Actions -->
    <v-row>
      <v-col cols="12" md="8">
        <v-card class="mb-4">
          <v-card-title>Recent Members</v-card-title>
          <v-card-text>
            <v-table v-if="recentMembers.length > 0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Join Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in recentMembers" :key="member.id">
                  <td>{{ member.first_name }} {{ member.last_name }}</td>
                  <td>{{ member.email }}</td>
                  <td>{{ member.phone }}</td>
                  <td>{{ formatDate(member.join_date) }}</td>
                </tr>
              </tbody>
            </v-table>
            <div v-else class="text-center py-6">
              <p class="text-medium-emphasis">No members found.</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Quick Actions</v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item
                v-for="action in quickActions"
                :key="action.title"
                :prepend-icon="action.icon"
                :title="action.title"
                :to="action.route"
                class="mb-2"
                rounded="lg"
              ></v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const auth = useAuthStore();
const stats = ref({});
const recentMembers = ref([]);
const loading = ref(false);

const quickActions = ref([
  { title: 'Add New Member', icon: 'mdi-account-plus', route: { name: 'members.create' } },
  { title: 'Schedule Event', icon: 'mdi-calendar-plus', route: { name: 'events.create' } },
  { title: 'View Reports', icon: 'mdi-chart-bar', route: '#' },
  { title: 'Send Announcement', icon: 'mdi-email-send', route: '#' }
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    // Fetch stats
    const statsResponse = await api.getStats();
    stats.value = statsResponse.data;

    // Fetch recent members
    const membersResponse = await api.getMembers({ limit: 5 });
    recentMembers.value = membersResponse.data.data || [];
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>
