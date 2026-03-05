# Critical API Issues - Root Cause & Fixes

## Issue #1: Events SoftDelete Column Missing ❌

### Problem
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'events.deleted_at' 
in 'where clause' ... SQL: select count(*) as aggregate from `events` 
where `events`.`deleted_at` is null
```

### Root Cause
- `Event` model uses `use SoftDeletes;` trait
- SoftDeletes automatically filters deleted_at in queries
- But the migration `2026_01_19_103000_create_events_table.php` **never created the deleted_at column**
- Mismatch = queries fail when SoftDeletes tries to check for null deleted_at

### Solution ✅
Created migration: `2026_03_04_000000_add_deleted_at_to_events_table.php`

```php
Schema::table('events', function (Blueprint $table) {
    $table->softDeletes();  // Adds deleted_at column
});
```

### How to Apply
```bash
cd c:\projects\church-management
php artisan migrate
```

---

## Issue #2: Member "not found or permission denied" Error ❌

### Problem
```json
{
    "success": false,
    "message": "Member not found or you do not have permission to view this member"
}
```

Occurs in `MemberController::show($id)` method when querying:
```php
$member = Member::with(['church', 'attendances.event'])
    ->where('church_id', $churchId)
    ->find($id);
```

### Root Cause Analysis
Three possible causes:
1. **Authentication issue**: `auth()->user()` returns null
2. **Missing church_id**: User exists but has no `church_id` set
3. **Wrong church_id**: User's church_id doesn't match member's church_id

### Solution ✅

#### A. Added Logging
Updated `MemberController::getCurrentChurchId()` with detailed logging:

```php
private function getCurrentChurchId()
{
    $user = auth()->user();

    if (!$user) {
        \Log::error('getCurrentChurchId: No authenticated user');
        return null;
    }

    if ($user->church_id) {
        \Log::info('getCurrentChurchId: Found church_id=' . $user->church_id);
        return $user->church_id;
    }

    if ($user->church) {
        \Log::info('getCurrentChurchId: Found church through relationship');
        return $user->church->id;
    }

    \Log::warning('getCurrentChurchId: User has no church_id or relationship');
    return null;
}
```

#### B. Added Diagnostic Endpoints
Created `DiagnosticController` with two endpoints:

**1. Check Authentication:**
```
GET /api/diagnostic/auth (with Bearer token)

Response:
{
    "authenticated": true,
    "user_id": 1,
    "user_email": "admin@example.com",
    "user_church_id": 1,
    "user_role": "admin",
    "token": "Present"
}
```

**2. Debug Member Lookup:**
```
GET /api/diagnostic/member/{id} (with Bearer token)

Response:
{
    "status": "ok",
    "user_church_id": 1,
    "member_church_id": 1,
    "member_exists": true,
    "church_match": true,
    "member": { ... member data ... }
}
```

### How to Diagnose

#### Step 1: Test Authentication
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/auth
```

If `authenticated: false`, token is invalid or expired.

#### Step 2: Check User Church ID
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/diagnostic/member/1
```

Check:
- `user_church_id`: Is it populated?
- `member_exists`: Does member with ID 1 exist?
- `church_match`: Do they have same church?

#### Step 3: View Logs
```bash
tail -f c:\projects\church-management\storage\logs\laravel.log
```

Look for:
- `getCurrentChurchId: No authenticated user` = Auth issue
- `getCurrentChurchId: Found church_id=X` = OK, user has church
- `getCurrentChurchId: User has no church_id or relationship` = User not linked to church

---

## Quick Fixes Checklist

### For SoftDelete Error
- [x] Create migration to add `deleted_at` column
- [ ] Run `php artisan migrate`
- [ ] Verify: `mysql> DESCRIBE events;` shows `deleted_at` column

### For Member Permission Error

**Option 1: Check if user has church_id**
```sql
SELECT id, email, church_id FROM users WHERE id = YOUR_USER_ID;
```

If `church_id` is NULL, update:
```sql
UPDATE users SET church_id = 1 WHERE id = YOUR_USER_ID;
```

**Option 2: Use diagnostic endpoints**
- Call `/api/diagnostic/auth` to see actual user data
- Call `/api/diagnostic/member/{id}` to debug specific member lookup
- Check logs for detailed error messages

**Option 3: Check member exists in correct church**
```sql
SELECT id, first_name, last_name, church_id FROM members WHERE id = 1;
```

If `church_id` doesn't match user's `church_id`, that's the issue.

---

## Files Modified

### 1. `database/migrations/2026_03_04_000000_add_deleted_at_to_events_table.php` ✅ NEW
- Adds `deleted_at` column to events table
- Required by SoftDeletes trait in Event model

### 2. `app/Http/Controllers/Api/DiagnosticController.php` ✅ NEW
- `/api/diagnostic/auth` - Check authentication
- `/api/diagnostic/member/{id}` - Debug member lookups

### 3. `app/Http/Controllers/Api/MemberController.php` ✅ UPDATED
- Added logging to `getCurrentChurchId()`
- Helps identify auth/church_id issues

### 4. `routes/api.php` ✅ UPDATED
- Added diagnostic routes
- Protected with auth:sanctum middleware

---

## Testing

### Test 1: Events SoftDelete
```bash
php artisan tinker
>>> \App\Models\Event::count()  # Should work now
```

### Test 2: Member Lookup
```bash
# With valid token
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/members/1
```

### Test 3: Full Workflow
1. Login to get token
2. Call `/api/diagnostic/auth` to verify token
3. Call `/api/diagnostic/member/1` to test member lookup
4. Call `/api/members/1` for actual member data

---

## Summary

**SoftDelete Issue**: Mismatch between model trait and database schema → Fixed with migration

**Permission Issue**: Likely either:
- User has no church_id → User setup issue
- Token invalid → Auth issue
- Member in different church → Data issue

**Use diagnostic endpoints to identify exact cause**, then fix accordingly.
