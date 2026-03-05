# Church Management Dashboard - Real Data Integration Guide

## Overview

Your Vue.js dashboard (`resources/js/views/dashboard/Index.vue`) has been updated to fetch and display real data from your Laravel backend API. This guide explains the integration and how to extend it.

---

## Current Implementation

### 1. **Stats Section** - Real-Time Dashboard Statistics

**API Endpoint:** `GET /api/dashboard/stats`

**Data Retrieved:**
```javascript
{
  members: {
    total: 145,              // Total members
    active: 132,             // Active members
    new_this_month: 12,      // New members this month
    growth_percentage: 15.5  // Growth compared to last month
  },
  events: {
    total: 24,               // Total events
    upcoming: 8,             // Upcoming events
    this_month: 5            // Events this month
  },
  attendance: {
    this_month: 1200,        // Total attendance this month
    last_month: 980,         // Last month for comparison
    growth_percentage: 22.4  // Attendance growth
  },
  system: {
    status: 'online',        // System status
    last_backup: '2026-03-03 14:30:00',  // Last backup time
    storage_used: '75%'      // Storage usage
  }
}
```

**Display:** Four cards showing key metrics with growth indicators

---

### 2. **Recent Members Section** - Latest Additions

**API Endpoint:** `GET /api/members/recent?limit=5`

**Data Retrieved:**
```javascript
[
  {
    id: 1,
    first_name: "John",
    last_name: "Doe",
    email: "john@example.com",
    phone: "555-1234",
    join_date: "2026-03-01",
    membership_status: "active",
    occupation: "Engineer",
    avatar_color: "primary",
    initials: "JD",
    created_at: "2026-03-01T10:30:00Z",
    time_ago: "2 days ago"
  }
]
```

**Display:** List of 5 most recent members with avatars and join dates

---

### 3. **Upcoming Events Section** - Next 5 Events

**API Endpoint:** `GET /api/events/upcoming?limit=5`

**Data Retrieved:**
```javascript
[
  {
    id: 1,
    title: "Sunday Service",
    type: "service",
    start_date: "2026-03-08 10:00:00",
    end_date: "2026-03-08 12:00:00",
    location: "Main Hall",
    description: "Weekly worship service",
    total_attendance: 85,
    days_until: 4,
    color: "primary",
    icon: "mdi-church"
  }
]
```

**Display:** List of 5 upcoming events with dates, locations, and event type icons

---

## API Integration Features

### Error Handling
- Graceful error handling with toast notifications
- Console logging for debugging
- Continues to display interface even if API calls fail

### Authentication
- Automatically retrieves JWT token from localStorage
- Passes token in Authorization header: `Bearer {token}`
- Works with Sanctum authentication

### Auto-Refresh
- Data refreshes automatically every 5 minutes
- Proper cleanup on component unmount
- Loading state management

### Performance
- Parallel API calls using `Promise.all()`
- Efficient data transformation
- Optimized queries with limit parameters

---

## Data Fetching Functions

### Main Function
```javascript
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
```

### Individual Functions
Each function handles its own API call and error handling:
- `fetchDashboardStats()` - Gets all dashboard statistics
- `fetchRecentMembers()` - Gets 5 most recent members
- `fetchUpcomingEvents()` - Gets 5 upcoming events

---

## Adding New Data Sources

### Example: Add Prayer Requests

1. **Add to reactive data:**
```javascript
const recentPrayerRequests = ref([])
```

2. **Create fetch function:**
```javascript
const fetchPrayerRequests = async () => {
  try {
    const client = getApiClient()
    const response = await client.get('/prayer-requests/recent', {
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

3. **Add to main fetch:**
```javascript
const fetchDashboardData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchDashboardStats(),
      fetchRecentMembers(),
      fetchUpcomingEvents(),
      fetchPrayerRequests()  // Add here
    ])
  } finally {
    loading.value = false
  }
}
```

4. **Add to template:**
```vue
<v-card>
  <v-card-title>Recent Prayer Requests</v-card-title>
  <v-card-text>
    <v-list v-if="recentPrayerRequests.length > 0">
      <v-list-item
        v-for="prayer in recentPrayerRequests"
        :key="prayer.id"
      >
        <v-list-item-title>{{ prayer.title }}</v-list-item-title>
        <v-list-item-subtitle>{{ prayer.description }}</v-list-item-subtitle>
      </v-list-item>
    </v-list>
    <div v-else class="text-center py-8">
      <p class="text-medium-emphasis">No prayer requests</p>
    </div>
  </v-card-text>
</v-card>
```

---

## Backend Requirements

### Ensure these endpoints exist in your Laravel API:

#### Required
- ✅ `GET /api/dashboard/stats` - Dashboard statistics
- ✅ `GET /api/members/recent` - Recent members list
- ✅ `GET /api/events/upcoming` - Upcoming events

#### Optional (Already Implemented)
- `GET /api/dashboard/activity` - Recent activity feed
- `GET /api/dashboard/attendance-trends` - Attendance data over time
- `GET /api/dashboard/member-distribution` - Member demographics

All endpoints must return:
```json
{
  "success": true,
  "data": { /* Your data */ }
}
```

---

## Customization Examples

### Change Refresh Interval
Currently set to 5 minutes. To change to 2 minutes:
```javascript
// Line in onMounted
refreshInterval = setInterval(fetchDashboardData, 2 * 60 * 1000)
```

### Add Loading Spinner
Wrap the content in a v-progress-linear:
```vue
<v-progress-linear v-if="loading" indeterminate color="primary"></v-progress-linear>
```

### Format Numbers
Add formatters for large numbers:
```javascript
const formatNumber = (num) => {
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k'
  return num.toString()
}
```

Then use: `{{ formatNumber(dashboardStats.members.total) }}`

---

## Testing

### Via Browser Console
```javascript
// Test API connectivity
fetch('/api/dashboard/stats', {
  headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
})
.then(r => r.json())
.then(d => console.log(d))
```

### Via API Testing Tool (Postman)
1. Get your auth token (login response)
2. Set header: `Authorization: Bearer {token}`
3. GET to: `http://localhost:8000/api/dashboard/stats`

---

## Common Issues & Solutions

### Issue: Data not loading
**Solution:** 
- Check browser console for errors
- Verify token is stored in localStorage
- Ensure API endpoints exist and return correct JSON

### Issue: CORS errors
**Solution:**
- Check CORS configuration in Laravel
- Ensure API is being called from correct domain

### Issue: Stale data
**Solution:**
- Reduce refresh interval
- Manually trigger `fetchDashboardData()` function

### Issue: Too many API calls
**Solution:**
- Increase refresh interval
- Implement caching in the API layer

---

## Best Practices

1. **Always check `response.data.success`** before using data
2. **Validate array data** with `Array.isArray()` check
3. **Handle null/undefined** values with optional chaining (`?.`)
4. **Use consistent error messages** for user feedback
5. **Test with no auth token** to handle unauthenticated scenarios
6. **Monitor API response times** and optimize queries if slow

---

## Future Enhancements

- [ ] Implement Chart.js for attendance trends visualization
- [ ] Add member distribution pie chart
- [ ] Real-time WebSocket updates instead of polling
- [ ] Caching with Service Workers
- [ ] Export dashboard data to PDF/Excel
- [ ] Customizable dashboard widgets
- [ ] Mobile-optimized dashboard view

---

## File Structure

```
resources/
├── js/
│   ├── views/
│   │   ├── dashboard/
│   │   │   └── Index.vue (Your dashboard component)
│   └── stores/
│       └── auth.ts (Auth store with user/church data)
```

---

**Last Updated:** March 4, 2026
**Status:** ✅ Production Ready
