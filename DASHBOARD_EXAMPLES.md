# Dashboard Extension Examples

This file provides practical examples for extending your Church Management Dashboard with additional data sources.

---

## Example 1: Adding Prayer Requests Widget

### Backend (Laravel Controller)

```php
// app/Http/Controllers/Api/DashboardController.php

public function recentPrayerRequests()
{
    $prayers = PrayerRequest::with('member')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get()
        ->map(function ($prayer) {
            return [
                'id' => $prayer->id,
                'title' => $prayer->title,
                'description' => $prayer->description,
                'member_name' => $prayer->member->first_name . ' ' . $prayer->member->last_name,
                'priority' => $prayer->priority, // high, medium, low
                'status' => $prayer->status, // active, answered
                'created_at' => $prayer->created_at,
                'time_ago' => $prayer->created_at->diffForHumans(),
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $prayers
    ]);
}
```

### Route (routes/api.php)

```php
Route::get('/dashboard/prayer-requests', [DashboardController::class, 'recentPrayerRequests']);
```

### Frontend (Vue Component)

**1. Add to reactive data:**
```javascript
const recentPrayerRequests = ref([])
```

**2. Create fetch function:**
```javascript
const fetchPrayerRequests = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/dashboard/prayer-requests', {
      params: { limit: 5 }
    })
    
    if (response.data.success && Array.isArray(response.data.data)) {
      recentPrayerRequests.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching prayer requests:', error)
  }
}
```

**3. Add to main fetch function:**
```javascript
const fetchDashboardData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchDashboardStats(),
      fetchRecentMembers(),
      fetchUpcomingEvents(),
      fetchPrayerRequests()  // Add this line
    ])
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}
```

**4. Add to template:**
```vue
<v-col cols="12" lg="6">
  <v-card>
    <v-card-title class="d-flex justify-space-between align-center">
      <span>Recent Prayer Requests</span>
      <v-btn variant="text" to="/prayer-requests">View All</v-btn>
    </v-card-title>
    <v-card-text>
      <v-list lines="two" v-if="recentPrayerRequests.length > 0">
        <v-list-item
          v-for="prayer in recentPrayerRequests"
          :key="prayer.id"
          :to="`/prayer-requests/${prayer.id}`"
        >
          <template #prepend>
            <v-chip
              :color="prayer.priority === 'high' ? 'error' : prayer.priority === 'medium' ? 'warning' : 'info'"
              small
            >
              {{ prayer.priority }}
            </v-chip>
          </template>
          <v-list-item-title>{{ prayer.title }}</v-list-item-title>
          <v-list-item-subtitle>
            {{ prayer.member_name }} • {{ prayer.time_ago }}
          </v-list-item-subtitle>
        </v-list-item>
      </v-list>
      <div v-else class="text-center py-8">
        <v-icon size="48" color="grey-lighten-1" class="mb-4">mdi-hand-heart</v-icon>
        <p class="text-medium-emphasis">No prayer requests</p>
      </div>
    </v-card-text>
  </v-card>
</v-col>
```

---

## Example 2: Adding Donations Chart

### Backend

```php
// app/Http/Controllers/Api/DashboardController.php

public function donationStats(Request $request)
{
    $period = $request->get('period', 'month'); // week, month, year
    $data = [];

    if ($period === 'week') {
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $amount = Donation::whereDate('created_at', $date)->sum('amount');
            $data[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'amount' => $amount,
            ];
        }
    } elseif ($period === 'month') {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        $donations = Donation::whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as amount')
            ->get();

        $allData = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $donation = $donations->firstWhere('date', $dateStr);
            $allData[] = [
                'date' => $dateStr,
                'day' => $current->format('M d'),
                'amount' => $donation?->amount ?? 0,
            ];
            $current->addDay();
        }
        $data = $allData;
    }

    $total = collect($data)->sum('amount');

    return response()->json([
        'success' => true,
        'data' => [
            'chart_data' => $data,
            'total' => $total,
            'period' => $period,
        ]
    ]);
}
```

### Route

```php
Route::get('/dashboard/donations', [DashboardController::class, 'donationStats']);
```

### Frontend with Chart.js

**1. Install Chart.js:**
```bash
npm install chart.js vue-chartjs
```

**2. Add to reactive data:**
```javascript
const donationData = ref({
  chart_data: [],
  total: 0,
  period: 'month'
})
const donationPeriod = ref('month')
```

**3. Create fetch function:**
```javascript
const fetchDonationStats = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/dashboard/donations', {
      params: { period: donationPeriod.value }
    })
    
    if (response.data.success) {
      donationData.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching donation stats:', error)
  }
}

// Computed for chart data
const donationChartData = computed(() => {
  return {
    labels: donationData.value.chart_data.map(d => d.day),
    datasets: [{
      label: 'Donations',
      data: donationData.value.chart_data.map(d => d.amount),
      backgroundColor: 'rgba(76, 175, 80, 0.1)',
      borderColor: 'rgba(76, 175, 80, 1)',
      tension: 0.4,
      fill: true,
    }]
  }
})

const donationChartOptions = {
  responsive: true,
  plugins: {
    legend: { display: false },
    title: { display: false }
  },
  scales: {
    y: { beginAtZero: true }
  }
}
```

**4. Add to template:**
```vue
<v-col cols="12">
  <v-card>
    <v-card-title class="d-flex justify-space-between align-center">
      <span>Donations Trend</span>
      <v-select
        v-model="donationPeriod"
        :items="[
          { title: 'This Week', value: 'week' },
          { title: 'This Month', value: 'month' },
          { title: 'This Year', value: 'year' }
        ]"
        density="compact"
        variant="outlined"
        @update:modelValue="fetchDonationStats"
        style="max-width: 150px;"
      ></v-select>
    </v-card-title>
    <v-card-text>
      <div class="mb-4">
        <h3 class="text-h6">Total Donations: ${{ donationData.total.toFixed(2) }}</h3>
      </div>
      <Line :data="donationChartData" :options="donationChartOptions" />
    </v-card-text>
  </v-card>
</v-col>

<script setup>
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)
</script>
```

---

## Example 3: Adding Member Statistics with Filters

### Frontend Only (Uses Existing API)

```javascript
// Reactive data
const memberStats = ref({
  by_gender: {},
  by_marital_status: {},
  by_status: {}
})

// Fetch member distribution
const fetchMemberDistribution = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/dashboard/member-distribution')
    
    if (response.data.success) {
      memberStats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching member distribution:', error)
  }
}

// Add to fetchDashboardData()
```

### Template

```vue
<v-row>
  <v-col cols="12" md="4">
    <v-card>
      <v-card-title>Members by Gender</v-card-title>
      <v-card-text>
        <v-simple-table dense>
          <tbody>
            <tr v-for="(count, gender) in memberStats.by_gender" :key="gender">
              <td>{{ gender }}</td>
              <td class="text-right font-weight-bold">{{ count }}</td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card-text>
    </v-card>
  </v-col>

  <v-col cols="12" md="4">
    <v-card>
      <v-card-title>Members by Status</v-card-title>
      <v-card-text>
        <v-simple-table dense>
          <tbody>
            <tr v-for="(count, status) in memberStats.by_status" :key="status">
              <td>{{ status }}</td>
              <td class="text-right font-weight-bold">{{ count }}</td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card-text>
    </v-card>
  </v-col>

  <v-col cols="12" md="4">
    <v-card>
      <v-card-title>Members by Marital Status</v-card-title>
      <v-card-text>
        <v-simple-table dense>
          <tbody>
            <tr v-for="(count, status) in memberStats.by_marital_status" :key="status">
              <td>{{ status }}</td>
              <td class="text-right font-weight-bold">{{ count }}</td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-card-text>
    </v-card>
  </v-col>
</v-row>
```

---

## Example 4: Adding Notifications Alert

### Frontend

```javascript
// Reactive data
const notifications = ref([])
const unreadCount = ref(0)

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/notifications', {
      params: { 
        limit: 5,
        unread: true
      }
    })
    
    if (response.data.success && Array.isArray(response.data.data)) {
      notifications.value = response.data.data
      unreadCount.value = response.data.data.length
    }
  } catch (error) {
    console.error('Error fetching notifications:', error)
  }
}

// Mark as read
const markAsRead = async (notificationId) => {
  try {
    const client = getApiClient()
    await client.put(`/notifications/${notificationId}/read`)
    fetchNotifications() // Refresh list
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}
```

### Template

```vue
<v-card v-if="unreadCount > 0" class="mb-6 border-l-5 border-l-warning">
  <v-card-title class="d-flex justify-space-between align-center">
    <span>Notifications</span>
    <v-badge :content="unreadCount" color="warning">
      <v-icon>mdi-bell</v-icon>
    </v-badge>
  </v-card-title>
  <v-card-text>
    <v-list>
      <v-list-item v-for="notif in notifications" :key="notif.id" @click="markAsRead(notif.id)">
        <template #prepend>
          <v-icon :color="notif.type === 'error' ? 'error' : 'warning'">
            mdi-{{ notif.type === 'error' ? 'alert-circle' : 'information' }}
          </v-icon>
        </template>
        <v-list-item-title>{{ notif.title }}</v-list-item-title>
        <v-list-item-subtitle>{{ notif.message }}</v-list-item-subtitle>
      </v-list-item>
    </v-list>
  </v-card-text>
</v-card>
```

---

## Example 5: Real-Time Updates with WebSocket (Advanced)

```javascript
// Setup WebSocket
let ws = null

const setupWebSocket = () => {
  const token = localStorage.getItem('token')
  ws = new WebSocket(`ws://localhost:6001/app/${token}`)

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data)
    
    if (data.channel === 'dashboard') {
      // Update specific data based on event type
      switch(data.event) {
        case 'new-member':
          recentMembers.value.unshift(data.data)
          break
        case 'new-event':
          upcomingEvents.value.unshift(data.data)
          break
        case 'attendance-recorded':
          dashboardStats.value.attendance.this_month += data.data.total
          break
      }
    }
  }

  ws.onerror = (error) => {
    console.error('WebSocket error:', error)
  }
}

// Call in onMounted
onMounted(() => {
  // ... existing code ...
  setupWebSocket()
})

// Cleanup in onUnmounted
onUnmounted(() => {
  if (ws) ws.close()
  // ... existing cleanup ...
})
```

---

## Best Practices for Dashboard Extensions

1. **Always check response.data.success** before using data
2. **Use consistent error handling** across all fetch functions
3. **Implement loading states** for better UX
4. **Add proper pagination** for large datasets
5. **Cache data when possible** to reduce API calls
6. **Use computed properties** for derived data
7. **Validate data types** before displaying
8. **Test with no data** to ensure graceful fallbacks
9. **Monitor performance** - watch for N+1 query problems
10. **Document your additions** with comments

---

**Need help?** See DASHBOARD_INTEGRATION_GUIDE.md for more information
