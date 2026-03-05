# API Standards & Documentation

## 📋 Response Format Standard

All API endpoints **MUST** return responses in this format:

### Success Response (2xx)
```json
{
  "success": true,
  "message": "Action completed successfully",
  "data": { /* actual data */ },
  "meta": { /* pagination/extra info */ }
}
```

### Error Response (4xx, 5xx)
```json
{
  "success": false,
  "message": "Human readable error message",
  "errors": { /* validation errors */ }
}
```

---

## 🔐 Authentication

### Login Endpoint
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123def456...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin",
      "church_id": 1
    },
    "church": {
      "id": 1,
      "name": "Grace Church",
      "email": "grace@example.com"
    }
  }
}
```

### Token Usage
Include token in all subsequent requests:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 👥 Members Endpoints

### List Members
```http
GET /api/members?page=1&limit=15&status=active&search=john
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Members retrieved successfully",
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "555-1234",
      "membership_status": "active",
      "join_date": "2026-01-15",
      "birth_date": "1990-03-20",
      "gender": "Male",
      "marital_status": "Married",
      "occupation": "Engineer",
      "address": "123 Main St",
      "city": "Springfield",
      "state": "IL",
      "zip_code": "62701",
      "created_at": "2026-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 145,
    "per_page": 15,
    "current_page": 1,
    "last_page": 10
  }
}
```

### Create Member
```http
POST /api/members
Authorization: Bearer {token}
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "phone": "555-5678",
  "birth_date": "1992-06-15",
  "join_date": "2026-03-01",
  "membership_status": "active",
  "gender": "Female",
  "marital_status": "Single",
  "occupation": "Doctor",
  "address": "456 Oak Ave",
  "city": "Springfield",
  "state": "IL",
  "zip_code": "62702"
}
```

### Update Member
```http
PUT /api/members/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.smith@example.com",
  "membership_status": "active"
}
```

### Delete Member
```http
DELETE /api/members/{id}
Authorization: Bearer {token}
```

### Member Statistics
```http
GET /api/members/stats
GET /api/members/stats/gender-distribution
GET /api/members/stats/marital-status
GET /api/members/stats/age-groups
GET /api/members/stats/join-trends
Authorization: Bearer {token}
```

---

## 📅 Events Endpoints

### List Events
```http
GET /api/events?page=1&type=service&status=upcoming
Authorization: Bearer {token}
```

### Create Event
```http
POST /api/events
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Sunday Service",
  "description": "Weekly Sunday worship service",
  "type": "service",
  "category": "worship",
  "start_date": "2026-03-08T10:00:00",
  "end_date": "2026-03-08T12:00:00",
  "location": "Main Hall",
  "capacity": 200,
  "recurring": false,
  "track_attendance": true,
  "send_notifications": true
}
```

### Record Attendance
```http
POST /api/events/{id}/attendance
Authorization: Bearer {token}
Content-Type: application/json

{
  "men": 45,
  "women": 52,
  "children": 28,
  "visitors": 15,
  "notes": "Good turnout"
}
```

### Get Upcoming Events
```http
GET /api/events/upcoming?limit=5
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Sunday Service",
      "type": "service",
      "start_date": "2026-03-08T10:00:00Z",
      "end_date": "2026-03-08T12:00:00Z",
      "location": "Main Hall",
      "description": "Weekly worship",
      "total_attendance": 140,
      "days_until": 4,
      "capacity": 200,
      "is_upcoming": true
    }
  ]
}
```

---

## 💰 Donations Endpoints

### List Donations
```http
GET /api/donations?page=1&status=verified&from=2026-01-01&to=2026-03-01
Authorization: Bearer {token}
```

### Record Donation
```http
POST /api/donations
Authorization: Bearer {token}
Content-Type: application/json

{
  "member_id": 5,
  "donation_type_id": 1,
  "amount": 100.00,
  "currency": "USD",
  "payment_method": "cash",
  "donation_date": "2026-03-01",
  "frequency": "one-time",
  "is_recurring": false,
  "notes": "Sunday offering"
}
```

### Donation Statistics
```http
GET /api/donations/stats
GET /api/donations/stats/by-type
GET /api/donations/stats/by-method
GET /api/donations/stats/monthly
Authorization: Bearer {token}
```

---

## 📊 Dashboard Endpoints

### Dashboard Statistics
```http
GET /api/dashboard/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "members": {
      "total": 145,
      "active": 132,
      "new_this_month": 12,
      "growth_percentage": 8.6
    },
    "events": {
      "total": 24,
      "upcoming": 8,
      "this_month": 3
    },
    "attendance": {
      "this_month": 2450,
      "last_month": 2100,
      "growth_percentage": 16.7
    },
    "donations": {
      "this_month": 5000.00,
      "last_month": 4200.00,
      "growth_percentage": 19.0
    },
    "system": {
      "status": "online",
      "last_backup": "2026-03-03 14:30:00",
      "storage_used": "42%"
    }
  }
}
```

### Recent Activity
```http
GET /api/dashboard/activity
Authorization: Bearer {token}
```

### Attendance Trends
```http
GET /api/dashboard/attendance-trends?period=month
Authorization: Bearer {token}
```

---

## 🔍 Search & Filter

### Member Search
```http
GET /api/members/search?q=john
GET /api/members/filter?status=active&gender=Male
GET /api/members/advanced-filter?age_min=18&age_max=65&marital_status=Married
Authorization: Bearer {token}
```

### Event Search
```http
GET /api/events/search?q=service
GET /api/events/filter?type=service&status=upcoming
Authorization: Bearer {token}
```

---

## 📥 Import & Export

### Export Members
```http
GET /api/members/export/excel
GET /api/members/export/csv
GET /api/members/export/pdf
Authorization: Bearer {token}
```

### Import Members
```http
POST /api/members/import
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: @members.xlsx
```

---

## ❌ Error Codes

| Code | Message | Cause |
|------|---------|-------|
| 200 | Success | Request succeeded |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Validation Error | Input validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal server error |

---

## 🔄 Pagination

All list endpoints support pagination:

**Parameters:**
- `page` - Current page (default: 1)
- `limit` - Items per page (default: 15, max: 100)
- `sort` - Sort field (e.g., `created_at`)
- `order` - Sort order (`asc` or `desc`)

**Example:**
```http
GET /api/members?page=2&limit=25&sort=name&order=asc
```

**Meta Response:**
```json
{
  "meta": {
    "total": 145,
    "per_page": 25,
    "current_page": 2,
    "last_page": 6,
    "from": 26,
    "to": 50
  }
}
```

---

## 🧪 Testing Endpoints

### Health Check
```http
GET /api/health
```

### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

### Update Profile
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "555-1234"
}
```

### Change Password
```http
PUT /api/user/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "old_password",
  "new_password": "new_password",
  "new_password_confirmation": "new_password"
}
```

---

## 📝 Validation Rules

### Member Creation
```php
'first_name' => 'required|string|max:255',
'last_name' => 'required|string|max:255',
'email' => 'nullable|email|unique:members',
'phone' => 'nullable|string|max:20',
'birth_date' => 'nullable|date',
'join_date' => 'required|date|before_or_equal:today',
'gender' => 'nullable|in:Male,Female,Other',
'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated',
'membership_status' => 'required|in:active,inactive,visitor,pending,transferred'
```

### Event Creation
```php
'title' => 'required|string|max:255',
'description' => 'nullable|string',
'type' => 'required|string|max:100',
'start_date' => 'required|date|after:now',
'end_date' => 'required|date|after_or_equal:start_date',
'location' => 'nullable|string|max:255',
'capacity' => 'nullable|integer|min:1',
'track_attendance' => 'boolean'
```

### Donation Creation
```php
'member_id' => 'nullable|exists:members,id',
'donation_type_id' => 'required|exists:donation_types,id',
'amount' => 'required|numeric|min:0.01|max:999999.99',
'currency' => 'required|string|size:3',
'payment_method' => 'required|in:cash,check,card,transfer,online,other',
'donation_date' => 'required|date|before_or_equal:today',
'frequency' => 'required|in:one-time,weekly,monthly,yearly'
```

---

## 🔑 API Keys & Rate Limiting

### Rate Limiting
- **Unauthenticated:** 60 requests/minute
- **Authenticated:** 300 requests/minute
- **Admin:** 1000 requests/minute

### Headers
```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 298
X-RateLimit-Reset: 1614556200
```

---

## 📚 Implementation Checklist

- [ ] All endpoints return standard format
- [ ] All endpoints validate input
- [ ] All endpoints check authentication
- [ ] All endpoints check authorization
- [ ] All endpoints handle errors gracefully
- [ ] All endpoints support pagination
- [ ] All endpoints have proper documentation
- [ ] All endpoints are tested
- [ ] Rate limiting is implemented
- [ ] CORS is properly configured

---

**Version:** 1.0
**Last Updated:** March 4, 2026
