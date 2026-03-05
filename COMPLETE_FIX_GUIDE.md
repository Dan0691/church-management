# Complete Guide: Resolving Dashboard & API Issues

## Issues Reported

1. **Events Error**: `Unknown column 'events.deleted_at'` in queries
2. **Members Error**: `"Member not found or you do not have permission to view this member"`

---

## ISSUE 1: Events SoftDelete Error ✅ FIXED

### What Happened
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'events.deleted_at' 
in 'where clause'
```

### Why It Happened
- `Event` model has `use SoftDeletes;` which automatically filters deleted records
- But the database migration never created the `deleted_at` column
- When querying, SoftDeletes tries to check `WHERE deleted_at IS NULL` but column doesn't exist

### How to Fix

**Step 1:** Run the migration
```bash
cd c:\projects\church-management
php artisan migrate
```

**Step 2:** Verify the column exists
```bash
# Option A: MySQL CLI
mysql -u root -p church_management -e "DESCRIBE events;"
# Look for 'deleted_at' column

# Option B: Laravel Tinker
php artisan tinker
>>> \App\Models\Event::count()  # Should work now
```

**What was added:**
- New migration: `database/migrations/2026_03_04_000000_add_deleted_at_to_events_table.php`
- This adds the `deleted_at` column that SoftDeletes expects

---

## ISSUE 2: Member Permission Error 🔍 DIAGNOSED

### What Happens
```json
{
    "success": false,
    "message": "Member not found or you do not have permission to view this member"
}
```

### Why It Happens
The controller checks:
```php
$member = Member::where('church_id', $churchId)->find($id);
```

This fails when:
1. **User not authenticated** - Token invalid or expired
2. **User has no church_id** - User not linked to a church
3. **Member in different church** - Member belongs to different church than user

### How to Diagnose

#### Method 1: Use Diagnostic Endpoints (RECOMMENDED)

**Check if user is authenticated:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/auth
```

Expected response (if working):
```json
{
    "authenticated": true,
    "user_id": 1,
    "user_email": "admin@example.com",
    "user_church_id": 1,
    "user_role": "admin",
    "token": "Present"
}
```

**If any of these are wrong, authentication is the issue.**

---

**Check specific member lookup:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/member/1
```

Expected response:
```json
{
    "status": "ok",
    "user_church_id": 1,
    "member_church_id": 1,
    "member_exists": true,
    "church_match": true,
    "member": {...member data...}
}
```

**Troubleshoot responses:**

| Response | Meaning | Fix |
|----------|---------|-----|
| `user_church_id: null` | User not linked to church | Run SQL: `UPDATE users SET church_id = 1 WHERE id = YOUR_ID;` |
| `member_exists: false` | Member with ID doesn't exist | Check member ID is correct |
| `church_match: false` | Member in different church | Check member was created in correct church |

---

#### Method 2: Check Logs

View Laravel logs for detailed error messages:
```bash
tail -f c:\projects\church-management\storage\logs\laravel.log
```

Look for messages like:
- `getCurrentChurchId: No authenticated user` = Token issue
- `getCurrentChurchId: Found church_id=1` = OK, user is linked
- `getCurrentChurchId: User has no church_id` = User not linked to church

---

#### Method 3: Direct Database Checks

**Check user has church_id:**
```sql
SELECT id, email, church_id FROM users WHERE id = YOUR_USER_ID;
```

If `church_id` is NULL:
```sql
UPDATE users SET church_id = 1 WHERE id = YOUR_USER_ID;
```

**Check member exists in correct church:**
```sql
SELECT id, first_name, last_name, church_id FROM members WHERE id = 1;
```

Compare:
- Member's `church_id` 
- User's `church_id` (from users table)
- They must match!

---

## Complete Testing Flow

### Before Starting
Make sure both servers are running:
```bash
# Terminal 1: Laravel (port 8000)
cd c:\projects\church-management
php artisan serve

# Terminal 2: Vue (port 5173)
cd c:\projects\church-management
npm run dev
```

### Full Test Sequence

**Step 1: Login and get token**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "your_password"
  }'
```

Response (save the token):
```json
{
    "access_token": "YOUR_TOKEN_HERE",
    "token_type": "Bearer",
    "expires_in": 60
}
```

**Step 2: Verify authentication**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/auth
```

Should show your user_id, email, church_id.

**Step 3: Test member lookup**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/member/1
```

Should show:
- `user_church_id`: Your church ID
- `member_church_id`: Member's church ID
- `church_match`: true

**Step 4: Get actual member data**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/members/1
```

Should return member data without "not found" error.

**Step 5: Test in Vue**
Go to http://localhost:5173/members and verify:
- ✅ Members load with data
- ✅ Stats cards show numbers
- ✅ Can create/edit/delete members

---

## Summary of Changes

### Files Modified

1. **`database/migrations/2026_03_04_000000_add_deleted_at_to_events_table.php`** (NEW)
   - Adds `deleted_at` column to events table
   - Required for SoftDeletes to work

2. **`app/Http/Controllers/Api/DiagnosticController.php`** (NEW)
   - Two endpoints for debugging
   - `/api/diagnostic/auth` - Check authentication
   - `/api/diagnostic/member/{id}` - Debug member lookups

3. **`app/Http/Controllers/Api/MemberController.php`** (UPDATED)
   - Added logging to `getCurrentChurchId()`
   - Helps identify auth/church_id issues

4. **`app/Http/Controllers/Api/EventController.php`** (UPDATED)
   - Added logging to `getCurrentChurchId()`
   - Consistent error tracking across controllers

5. **`routes/api.php`** (UPDATED)
   - Added diagnostic routes
   - Accessible with Bearer token authentication

---

## Common Fixes

### Fix 1: User not linked to church
```sql
-- Find user with no church
SELECT id, email, church_id FROM users WHERE church_id IS NULL;

-- Link user to church (replace 1 with correct church_id)
UPDATE users SET church_id = 1 WHERE id = 2;
```

### Fix 2: Token expired
Simply logout and login again to get new token.

### Fix 3: Member in wrong church
```sql
-- Find member
SELECT id, first_name, last_name, church_id FROM members WHERE id = 1;

-- Update to correct church (if needed)
UPDATE members SET church_id = 1 WHERE id = 1;
```

### Fix 4: Events table missing deleted_at
```bash
php artisan migrate
```

---

## Verification Checklist

- [ ] SoftDelete migration ran: `php artisan migrate`
- [ ] Users have church_id set: `SELECT * FROM users WHERE church_id IS NULL;` returns 0 rows
- [ ] Members are in correct church: `SELECT COUNT(*) FROM members WHERE church_id = 1;` returns > 0
- [ ] Authentication works: `/api/diagnostic/auth` returns authenticated: true
- [ ] Member lookup works: `/api/diagnostic/member/1` returns church_match: true
- [ ] Vue dashboard loads: http://localhost:5173/members shows data

---

## Need More Help?

1. **Check logs**: `tail -f storage/logs/laravel.log`
2. **Use diagnostic endpoints**: They show exactly what's wrong
3. **Run database checks**: SQL queries above
4. **Verify migrations**: `php artisan migrate:status`

Everything should be working now! 🎉
