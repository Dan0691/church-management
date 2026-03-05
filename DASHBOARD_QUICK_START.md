# Dashboard Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 1: Verify Your API is Running
```bash
# Make sure Laravel is running
php artisan serve
```

### Step 2: Check the Dashboard Loads
- Visit: `http://localhost/dashboard`
- You should see the dashboard with real data loading

### Step 3: Verify Data is Showing
Open your browser's Developer Console (F12):
```javascript
// Check if data is loaded
console.log('Dashboard Stats:', dashboardStats)
console.log('Recent Members:', recentMembers)
console.log('Upcoming Events:', upcomingEvents)
```

### Step 4: Check Network Requests
- Open Network tab in DevTools
- Look for these API calls:
  - `GET /api/dashboard/stats`
  - `GET /api/members/recent`
  - `GET /api/events/upcoming`

### Step 5: Add Your First Extension
Follow Example 1 from `DASHBOARD_EXAMPLES.md` to add Prayer Requests widget.

---

## 📊 What Data is Displayed

| Widget | Data Source | Updates |
|--------|-------------|---------|
| Quick Stats Cards | `/api/dashboard/stats` | Every 5 minutes |
| Recent Members List | `/api/members/recent` | Every 5 minutes |
| Upcoming Events List | `/api/events/upcoming` | Every 5 minutes |
| System Status | `/api/dashboard/stats` | Every 5 minutes |

---

## 🔧 Common Tasks

### Change Refresh Interval
Edit `Index.vue` line ~585:
```javascript
// Change 5 to your desired minutes
refreshInterval = setInterval(fetchDashboardData, 5 * 60 * 1000)
```

### Change Member Limit
Edit the fetch function call in `fetchRecentMembers()`:
```javascript
params: { limit: 10 }  // Change from 5 to 10
```

### Add Toast Notification
```javascript
toast.success('Data loaded!')
toast.error('Failed to load data')
toast.warning('Something went wrong')
toast.info('For your information')
```

### Manually Refresh Data
```javascript
// In browser console
fetchDashboardData()
```

---

## ✅ Checklist for Your First Extension

- [ ] Create new API endpoint in Laravel
- [ ] Add controller method that returns JSON
- [ ] Add route to `routes/api.php`
- [ ] Test endpoint in Postman with your token
- [ ] Add reactive data ref to Vue component
- [ ] Create fetch function in Vue component
- [ ] Add fetch to Promise.all() in fetchDashboardData()
- [ ] Add template to display data
- [ ] Test in browser
- [ ] Document the extension

---

## 🐛 Troubleshooting

### "No data showing"
1. Open DevTools → Network tab
2. Reload page
3. Check if API calls are returning data
4. Check response format is `{ success: true, data: {...} }`

### "API returns 401"
1. Make sure you're logged in
2. Check token is saved in localStorage
3. Try logging out and back in

### "API returns 404"
1. Check API route exists in `routes/api.php`
2. Check controller method exists
3. Check method name matches route

### "Data not updating"
1. Check if API is returning new data
2. Increase refresh interval for testing
3. Check browser console for errors

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `DASHBOARD_INTEGRATION_GUIDE.md` | Complete integration documentation |
| `DASHBOARD_CHANGES.md` | What was changed in the dashboard |
| `DASHBOARD_EXAMPLES.md` | Code examples for extensions |
| `DASHBOARD_QUICK_START.md` | This file |

---

## 🎯 Next Steps

1. **Test the current dashboard** - Make sure data loads
2. **Explore the code** - Understand how it works
3. **Add your first widget** - Follow the examples
4. **Implement charts** - Add Chart.js visualizations
5. **Add real-time updates** - Use WebSocket for live data
6. **Optimize performance** - Cache data, reduce API calls

---

## 💡 Tips

✅ **Always validate API response format** before using data
✅ **Test error scenarios** - What if API is slow?
✅ **Use meaningful variable names** - Makes code easier to maintain
✅ **Add comments** - Future you will thank present you
✅ **Keep API calls separate** - Makes testing easier
✅ **Use try-catch** - Always handle errors
✅ **Check console** - Look for errors when debugging
✅ **Test with real data** - Makes sure it actually works

---

## 🚨 Important Security Notes

⚠️ **Never hardcode tokens** - Always use localStorage
⚠️ **Validate all API responses** - Don't trust unknown data
⚠️ **Use HTTPS in production** - Protect your data
⚠️ **Set proper CORS headers** - Prevent unauthorized access
⚠️ **Rate limit API endpoints** - Prevent abuse
⚠️ **Sanitize user data** - Before displaying in HTML

---

## 📞 Need Help?

1. Check the error message in console
2. Look at `DASHBOARD_EXAMPLES.md` for similar code
3. Verify API endpoint is working
4. Check Laravel logs: `storage/logs/laravel.log`
5. Read the Laravel documentation for your API framework

---

**Version:** 1.0
**Status:** ✅ Ready to Use
**Last Updated:** March 4, 2026

Happy coding! 🎉
