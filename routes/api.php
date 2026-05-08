<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ChurchController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SermonController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DiagnosticController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/church', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// Profile photo routes
Route::post('/user/upload-photo', [AuthController::class, 'uploadProfilePhoto']);
Route::delete('/user/remove-photo', [AuthController::class, 'removeProfilePhoto']);
// Keep the GET route for traditional form (optional)
Route::get('/register/church', function () {
    return redirect('/register'); // Redirect to Vue.js SPA registration page
});

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Diagnostic endpoints
Route::get('/diagnostic/auth', [DiagnosticController::class, 'checkAuth']);
Route::get('/diagnostic/member/{id}', [DiagnosticController::class, 'checkMember']);

// Protected routes with sanctum middleware
Route::middleware(['auth:sanctum'])->group(function () {
    // Diagnostic endpoints
    Route::get('/diagnostic/auth', [DiagnosticController::class, 'checkAuth']);
    Route::get('/diagnostic/member/{id}', [DiagnosticController::class, 'checkMember']);

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/activity', [DashboardController::class, 'recentActivity']);
        Route::get('/upcoming-events', [DashboardController::class, 'upcomingEvents']);
        Route::get('/recent-members', [DashboardController::class, 'recentMembers']);
        Route::get('/quick-stats', [DashboardController::class, 'quickStats']);
        Route::get('/attendance-trends', [DashboardController::class, 'attendanceTrends']);
        Route::get('/member-distribution', [DashboardController::class, 'memberDistribution']);
    });

    // Members CRUD with pagination and filtering
    Route::prefix('members')->group(function () {
        // Basic CRUD
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);


        // Route::get('/download-template', [MemberController::class, 'downloadTemplate']);

        // Bulk operations
        Route::post('/bulk-update', [MemberController::class, 'bulkUpdate']);
        Route::post('/bulk-delete', [MemberController::class, 'bulkDelete']);
        Route::post('/bulk-status', [MemberController::class, 'bulkStatus']);

        // Import/Export
        Route::post('/import', [MemberController::class, 'import']);
        Route::get('/template', [MemberController::class, 'downloadTemplate']);

        // Statistics and Reports
        Route::get('/stats', [MemberController::class, 'stats']);
        Route::get('/stats/age-groups', [MemberController::class, 'ageGroups']);
        Route::get('/stats/gender-distribution', [MemberController::class, 'genderDistribution']);
        Route::get('/stats/marital-status', [MemberController::class, 'maritalStatusStats']);
        Route::get('/stats/occupation', [MemberController::class, 'occupationStats']);
        Route::get('/stats/join-trends', [MemberController::class, 'joinTrends']);

        // Search and Filtering
        Route::get('/search', [MemberController::class, 'search']);
        Route::get('/filter', [MemberController::class, 'filter']);
        Route::get('/advanced-filter', [MemberController::class, 'advancedFilter']);

        // Recent and quick access
        Route::get('/recent', [MemberController::class, 'recent']);
        Route::get('/birthday-month', [MemberController::class, 'birthdayThisMonth']);
        Route::get('/new-this-month', [MemberController::class, 'newThisMonth']);

        // Reports
        Route::get('/report/pdf', [MemberController::class, 'generatePdfReport']);
        Route::get('/report/excel', [MemberController::class, 'generateExcelReport']);

        // Print
        Route::get('/print', [MemberController::class, 'print']);
        Route::get('/export/{format?}', [MemberController::class, 'export']);
        Route::get('/{id}', [MemberController::class, 'show']);
        Route::put('/{id}', [MemberController::class, 'update']);
        Route::delete('/{id}', [MemberController::class, 'destroy']);
    });

    // Events CRUD
    Route::prefix('events')->group(function () {
        // Basic CRUD
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{id}', [EventController::class, 'show']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);

        // Bulk operations
        Route::post('/bulk-delete', [EventController::class, 'bulkDelete']);
        Route::post('/bulk-update', [EventController::class, 'bulkUpdate']);

        // Statistics
        Route::get('/stats', [EventController::class, 'stats']);
        Route::get('/stats/upcoming', [EventController::class, 'upcomingStats']);
        Route::get('/stats/past', [EventController::class, 'pastStats']);
        Route::get('/stats/attendance', [EventController::class, 'attendanceStats']);
        Route::get('/stats/type-distribution', [EventController::class, 'typeDistribution']);
        Route::get('/stats/monthly', [EventController::class, 'monthlyStats']);

        // Search and Filtering
        Route::get('/search', [EventController::class, 'search']);
        Route::get('/filter', [EventController::class, 'filter']);
        Route::get('/upcoming', [EventController::class, 'upcoming']);
        Route::get('/past', [EventController::class, 'past']);
        Route::get('/today', [EventController::class, 'today']);
        Route::get('/this-week', [EventController::class, 'thisWeek']);
        Route::get('/this-month', [EventController::class, 'thisMonth']);

        // Categories
        Route::get('/categories', [EventController::class, 'categories']);
        Route::post('/categories', [EventController::class, 'storeCategory']);
        Route::delete('/categories/{id}', [EventController::class, 'deleteCategory']);

        // Templates
        Route::get('/templates', [EventController::class, 'templates']);
        Route::post('/templates', [EventController::class, 'storeTemplate']);
        Route::post('/templates/{id}/use', [EventController::class, 'useTemplate']);

        // Export/Import
        Route::get('/export/{format?}', [EventController::class, 'export']);
        Route::post('/import', [EventController::class, 'import']);

        // Print
        Route::get('/print', [EventController::class, 'print']);

        // Calendar
        Route::get('/calendar', [EventController::class, 'calendar']);
        Route::get('/calendar/{year}/{month}', [EventController::class, 'calendarMonth']);

        // Reminders
        Route::post('/{id}/remind', [EventController::class, 'sendReminder']);
        Route::get('/{id}/reminders', [EventController::class, 'getReminders']);
    });

    // Attendance Routes
    Route::prefix('attendance')->group(function() {
        // Basic CRUD
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/', [AttendanceController::class, 'store']);

        // routes that must not be swallowed by the {id} parameter
        Route::get('/export', [AttendanceController::class, 'exportEventAttendance']);
        Route::get('/stats', [AttendanceController::class, 'stats']);
        Route::get('/trends', [AttendanceController::class, 'trends']);
        Route::get('/monthly/{year?}/{month?}', [AttendanceController::class, 'monthly']);
        Route::get('/yearly/{year?}', [AttendanceController::class, 'yearly']);

        // Event-specific attendance
        Route::get('/event/{event_id}', [AttendanceController::class, 'getByEvent']);
        Route::get('/event/{event_id}/stats', [AttendanceController::class, 'eventStats']);

        // parameterized id routes with numeric constraint so words won't match
        Route::get('/{id}', [AttendanceController::class, 'show'])->where('id','[0-9]+');
        Route::put('/{id}', [AttendanceController::class, 'update'])->where('id','[0-9]+');
        Route::delete('/{id}', [AttendanceController::class, 'destroy'])->where('id','[0-9]+');

        // Bulk operations

        // Reports
        Route::get('/report/pdf', [AttendanceController::class, 'generatePdfReport']);
        Route::get('/report/excel', [AttendanceController::class, 'generateExcelReport']);
    });

    // Event Attendance (Alias for convenience)
    Route::post('/events/{id}/attend', [EventController::class, 'attend']);
    Route::get('/events/{id}/attendance', [AttendanceController::class, 'getByEvent']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/generate', [ReportController::class, 'generate']);
        Route::get('/members', [ReportController::class, 'membersReport']);
        Route::get('/events', [ReportController::class, 'eventsReport']);
        Route::get('/attendance', [ReportController::class, 'attendanceReport']);
        Route::get('/financial', [ReportController::class, 'financialReport']);
        Route::get('/custom', [ReportController::class, 'customReport']);
        Route::post('/custom', [ReportController::class, 'generateCustomReport']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::get('/count', [NotificationController::class, 'count']);
        Route::post('/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);

        // Send notifications
        Route::post('/send', [NotificationController::class, 'send']);
        Route::post('/broadcast', [NotificationController::class, 'broadcast']);
    });

    // Departments
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);

        // Member management
        Route::post('/{department}/members', [DepartmentController::class, 'addMember']);
        Route::post('/{department}/bulk-members', [DepartmentController::class, 'bulkAddMembers']);
        Route::delete('/{department}/members/{member}', [DepartmentController::class, 'removeMember']);
        Route::put('/{department}/members/{member}/role', [DepartmentController::class, 'updateMemberRole']);

        // Activities
        Route::get('/{department}/activities', [DepartmentController::class, 'getActivities']);
        Route::post('/{department}/activities', [DepartmentController::class, 'recordActivity']);

        // Reports
        Route::get('/{department}/report', [DepartmentController::class, 'generateReport']);
    });

    // Donations
Route::prefix('donations')->group(function () {
    Route::get('/', [DonationController::class, 'index']);
    Route::post('/', [DonationController::class, 'store']);
    Route::get('/{id}', [DonationController::class, 'show']);
    Route::put('/{id}', [DonationController::class, 'update']);
    Route::delete('/{id}', [DonationController::class, 'destroy']);
    Route::post('/{id}/verify', [DonationController::class, 'verify']);
    Route::get('/report/generate', [DonationController::class, 'generateReport']);

    // Donation Types
    Route::get('/types', [DonationController::class, 'types']);
    Route::post('/types', [DonationController::class, 'storeType']);
    Route::put('/types/{id}', [DonationController::class, 'updateType']);
    Route::delete('/types/{id}', [DonationController::class, 'destroyType']);
});

    // Prayer Requests
   // Add to api.php
    Route::prefix('prayer-requests')->group(function () {
        Route::get('/', [PrayerRequestController::class, 'index']);
        Route::post('/', [PrayerRequestController::class, 'store']);
        Route::get('/{id}', [PrayerRequestController::class, 'show']);
        Route::put('/{id}', [PrayerRequestController::class, 'update']);
        Route::delete('/{id}', [PrayerRequestController::class, 'destroy']);
        Route::post('/{id}/pray', [PrayerRequestController::class, 'markAsPrayed']);
        Route::post('/{id}/comment', [PrayerRequestController::class, 'addComment']);
        Route::post('/{id}/answer', [PrayerRequestController::class, 'addAnswer']);
        Route::get('/statistics', [PrayerRequestController::class, 'getStatistics']);
        Route::get('/urgent', [PrayerRequestController::class, 'getUrgentRequests']);
        Route::get('/recent', [PrayerRequestController::class, 'getRecentRequests']);
        Route::get('/export', [PrayerRequestController::class, 'exportPrayerRequests']);
    });

    // Sermons
    Route::prefix('sermons')->group(function () {
        Route::get('/', [SermonController::class, 'index']);
        Route::post('/', [SermonController::class, 'store']);
        Route::get('/{id}', [SermonController::class, 'show']);
        Route::put('/{id}', [SermonController::class, 'update']);
        Route::delete('/{id}', [SermonController::class, 'destroy']);
        Route::post('/{id}/view', [SermonController::class, 'incrementView']);
        Route::post('/{id}/download', [SermonController::class, 'incrementDownload']);
        Route::get('/series', [SermonController::class, 'getSeries']);
    });

    // Tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::post('/{id}/assign', [TaskController::class, 'assign']);
        Route::post('/{id}/complete', [TaskController::class, 'complete']);
        Route::post('/{id}/add-comment', [TaskController::class, 'addComment']);
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::get('/search', [TaskController::class, 'search']);
    });

    // Settings
    // Route::prefix('settings')->group(function () {
    //     Route::get('/', [SettingsController::class, 'index']);
    //     Route::put('/', [SettingsController::class, 'update']);
    //     Route::get('/general', [SettingsController::class, 'general']);
    //     Route::put('/general', [SettingsController::class, 'updateGeneral']);
    //     Route::get('/email', [SettingsController::class, 'email']);
    //     Route::put('/email', [SettingsController::class, 'updateEmail']);
    //     Route::post('/email/test', [SettingsController::class, 'testEmail']);
    //     Route::get('/appearance', [SettingsController::class, 'appearance']);
    //     Route::put('/appearance', [SettingsController::class, 'updateAppearance']);
    //     Route::get('/notifications', [SettingsController::class, 'notificationSettings']);
    //     Route::put('/notifications', [SettingsController::class, 'updateNotificationSettings']);

        // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/', [SettingsController::class, 'update']);
        Route::put('/{key}', [SettingsController::class, 'updateSetting']);
        Route::get('/category/{category}', [SettingsController::class, 'byCategory']);
        Route::post('/reset', [SettingsController::class, 'resetToDefault']);
        Route::post('/bulk', [SettingsController::class, 'bulkStore']);
        Route::post('/settings', [SettingsController::class, 'store']); // For single setting


        // Backup & Restore
        Route::post('/backup', [SettingsController::class, 'createBackup']);
        Route::get('/backup/list', [SettingsController::class, 'listBackups']);
        Route::post('/backup/restore', [SettingsController::class, 'restoreBackup']);
        Route::get('/backup/download/{filename}', [SettingsController::class, 'downloadBackup']);
        Route::delete('/backup/{filename}', [SettingsController::class, 'deleteBackup']);

        // Export/Import Data
        Route::post('/export-data', [SettingsController::class, 'exportData']);
        Route::post('/import-data', [SettingsController::class, 'importData']);

        // System
        Route::post('/clear-cache', [SettingsController::class, 'clearCache']);
        Route::post('/reset-settings', [SettingsController::class, 'resetSettings']);
        Route::get('/system-info', [SettingsController::class, 'systemInfo']);
    });

    // System
    Route::prefix('system')->group(function () {
        Route::get('/status', function () {
            return response()->json([
                'status' => 'online',
                'timestamp' => now(),
                'version' => config('app.version', '1.0.0'),
                'environment' => config('app.env'),
                'maintenance' => app()->isDownForMaintenance(),
            ]);
        });

        Route::get('/health', function () {
            try {
                DB::connection()->getPdo();
                $database = 'connected';
            } catch (\Exception $e) {
                $database = 'disconnected';
            }

            return response()->json([
                'app' => 'running',
                'database' => $database,
                'cache' => 'connected', // You can add cache check here
                'timestamp' => now(),
            ]);
        });

        Route::post('/sync', function () {
            // Trigger data sync
            return response()->json(['message' => 'Sync initiated', 'timestamp' => now()]);
        });
    });

    // Churches CRUD (admin only)
    // Route::prefix('churches')->group(function () {
    //     Route::get('/', [ChurchController::class, 'index']);
    //     Route::post('/', [ChurchController::class, 'store']);
    //     Route::get('/{church}', [ChurchController::class, 'show']);
    //     Route::put('/{church}', [ChurchController::class, 'update']);
    //     Route::delete('/{church}', [ChurchController::class, 'destroy']);
    //     Route::get('/{church}/stats', [ChurchController::class, 'stats']);
        // Route::get('/{church}/members', [ChurchController::class, 'members']);
    //     Route::get('/{church}/events', [ChurchController::class, 'events']);
    // })->middleware('admin');


        Route::put('/churches/{id}', [DashboardController::class, 'update']);
        Route::get('/churches/{id}', [DashboardController::class, 'show']);
        Route::post('/churches/{id}/upload-logo', [DashboardController::class, 'uploadLogo']);
        Route::delete('/churches/{id}/remove-logo', [DashboardController::class, 'removeLogo']);


    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [DashboardController::class, 'analyticsOverview']);
        Route::get('/member-growth', [DashboardController::class, 'memberGrowth']);
        Route::get('/attendance-growth', [DashboardController::class, 'attendanceGrowth']);
        Route::get('/event-participation', [DashboardController::class, 'eventParticipation']);
        Route::get('/demographics', [DashboardController::class, 'demographics']);
    });

    // Print routes
    Route::prefix('print')->group(function () {
        Route::get('/members', [MemberController::class, 'print']);
        Route::get('/events', [EventController::class, 'print']);
        Route::get('/attendance/{event_id}', [AttendanceController::class, 'print']);
        Route::get('/report/{type}', [ReportController::class, 'print']);
    });

    // File uploads
    Route::post('/upload', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::url($path),
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    });

    // Export all data
    Route::get('/export-all', function () {
        return response()->json([
            'message' => 'Export initiated',
            'links' => [
                'members' => route('api.members.export', ['format' => 'excel']),
                'events' => route('api.events.export', ['format' => 'excel']),
                'attendance' => route('api.attendance.export'),
            ]
        ]);
    });
});
