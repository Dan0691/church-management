<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Church;
use App\Models\Member;
use App\Models\Setting;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function stats()
    {
        $churchId = auth()->user()->church_id; // Get current user's church ID

        // Member stats
        $totalMembers = Member::where('church_id', $churchId)->count();
        $activeMembers = Member::where('church_id', $churchId)
                            ->where('membership_status', 'active')
                            ->count();
        $newThisMonth = Member::where('church_id', $churchId)
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->count();
        $newLastMonth = Member::where('church_id', $churchId)
                        ->whereMonth('created_at', date('m', strtotime('-1 month')))
                        ->whereYear('created_at', date('Y', strtotime('-1 month')))
                        ->count();
        $memberGrowth = $newLastMonth > 0 ? (($newThisMonth - $newLastMonth) / $newLastMonth) * 100 : 100;

        // Event stats
        $totalEvents = Event::where('church_id', $churchId)->count();
        $upcomingEvents = Event::where('church_id', $churchId)
                        ->where('start_date', '>', now())
                        ->count();
        $thisMonthEvents = Event::where('church_id', $churchId)
                            ->whereMonth('start_date', date('m'))
                            ->whereYear('start_date', date('Y'))
                            ->count();

        // Attendance stats
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        $monthAttendance = Attendance::where('church_id', $churchId)
                        ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
                        ->sum('total');

        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthAttendance = Attendance::where('church_id', $churchId)
                            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                            ->sum('total');

        $attendanceGrowth = $lastMonthAttendance > 0 ?
            (($monthAttendance - $lastMonthAttendance) / $lastMonthAttendance) * 100 : 100;

        return response()->json([
            'success' => true,
            'data' => [
                'members' => [
                    'total' => $totalMembers,
                    'active' => $activeMembers,
                    'new_this_month' => $newThisMonth,
                    'growth_percentage' => round($memberGrowth, 2),
                ],
                'events' => [
                    'total' => $totalEvents,
                    'upcoming' => $upcomingEvents,
                    'this_month' => $thisMonthEvents,
                ],
                'attendance' => [
                    'this_month' => $monthAttendance,
                    'last_month' => $lastMonthAttendance,
                    'growth_percentage' => round($attendanceGrowth, 2),
                ],
                'system' => [
                    'status' => 'online',
                    'last_backup' => now()->subDays(1)->format('Y-m-d H:i:s'),
                    'storage_used' => '75%',
                ]
            ]
        ]);
    }

    public function recentActivity()
    {
        // Get recent members
        $recentMembers = Member::with('church')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'type' => 'member',
                    'title' => 'New Member Added',
                    'description' => $member->first_name . ' ' . $member->other_name . ' ' . $member->last_name . ' joined the church',
                    'time' => $member->created_at->diffForHumans(),
                    'avatar' => $member->getAvatarColor(),
                    'initials' => strtoupper(substr($member->first_name, 0, 1) . substr($member->other_name, 0, 1) . substr($member->last_name, 0, 1)),
                ];
            });

        // Get recent events
        $recentEvents = Event::orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => 'event',
                    'title' => 'New Event Created',
                    'description' => $event->title . ' scheduled for ' . $event->start_date->format('M d, Y'),
                    'time' => $event->created_at->diffForHumans(),
                    'icon' => 'mdi-calendar',
                    'color' => $this->getEventColor($event->type),
                ];
            });

        // Get recent attendance
        $recentAttendance = Attendance::with('event')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'type' => 'attendance',
                    'title' => 'Attendance Recorded',
                    'description' => $attendance->total . ' people attended ' . ($attendance->event->title ?? 'Event'),
                    'time' => $attendance->created_at->diffForHumans(),
                    'icon' => 'mdi-account-group',
                    'color' => 'success',
                ];
            });

        // Combine and sort by timestamp
        $activities = collect()
            ->merge($recentMembers)
            ->merge($recentEvents)
            ->merge($recentAttendance)
            ->sortByDesc('time')
            ->values()
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    public function upcomingEvents()
    {
        $churchId = auth()->user()->church_id;

        $events = Event::where('church_id', $churchId)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'type' => $event->type,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'location' => $event->location,
                    'description' => $event->description,
                    'total_attendance' => $event->attendances->sum('total'),
                    'days_until' => now()->diffInDays($event->start_date),
                    'color' => $this->getEventColor($event->type),
                    'icon' => $this->getEventIcon($event->type),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    public function recentMembers()
    {
        $churchId = auth()->user()->church_id;

        $members = Member::where('church_id', $churchId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'other_name' => $member->other_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'join_date' => $member->join_date,
                    'membership_status' => $member->membership_status,
                    'occupation' => $member->occupation,
                    'avatar_color' => $this->getAvatarColor($member),
                    'initials' => strtoupper(substr($member->first_name, 0, 1) . substr($member->other_name, 0, 1) . substr($member->last_name, 0, 1)),
                    'created_at' => $member->created_at,
                    'time_ago' => $member->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    public function quickStats()
    {
        // This is a simplified version of stats() for quick display
        return $this->stats();
    }

    public function attendanceTrends(Request $request)
    {
        $period = $request->get('period', 'month');
        $data = [];

        if ($period === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $attendance = Attendance::whereDate('created_at', $date)->sum('total');
                $data[] = [
                    'date' => $date,
                    'attendance' => $attendance,
                    'day' => now()->subDays($i)->format('D'),
                ];
            }
        } elseif ($period === 'month') {
            // Last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $attendance = Attendance::whereDate('created_at', $date)->sum('total');
                $data[] = [
                    'date' => $date,
                    'attendance' => $attendance,
                    'day' => now()->subDays($i)->format('M d'),
                ];
            }
        } else {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('Y-m');
                $attendance = Attendance::whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->sum('total');
                $data[] = [
                    'date' => $month,
                    'attendance' => $attendance,
                    'month' => now()->subMonths($i)->format('M Y'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period,
        ]);
    }

    public function memberDistribution()
    {
        $distribution = [
            'by_status' => [
                'active' => Member::where('membership_status', 'active')->count(),
                'inactive' => Member::where('membership_status', 'inactive')->count(),
                'visitor' => Member::where('membership_status', 'visitor')->count(),
                'pending' => Member::where('membership_status', 'pending')->count(),
            ],
            'by_gender' => [
                'Male' => Member::where('gender', 'Male')->count(),
                'Female' => Member::where('gender', 'Female')->count(),
                'Other' => Member::where('gender', 'Other')->count(),
            ],
            'by_marital_status' => [
                'Single' => Member::where('marital_status', 'Single')->count(),
                'Married' => Member::where('marital_status', 'Married')->count(),
                'Divorced' => Member::where('marital_status', 'Divorced')->count(),
                'Widowed' => Member::where('marital_status', 'Widowed')->count(),
                'Separated' => Member::where('marital_status', 'Separated')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $distribution
        ]);
    }

    private function getEventColor($type)
    {
        $colors = [
            'service' => '#2196F3',
            'midweek' => '#9C27B0',
            'prayer' => '#4CAF50',
            'bible_study' => '#00BCD4',
            'youth' => '#FF9800',
            'children' => '#E91E63',
            'women' => '#9C27B0',
            'men' => '#3F51B5',
            'outreach' => '#009688',
            'social' => '#FF5722',
            'training' => '#673AB7',
            'conference' => '#00BCD4',
            'other' => '#607D8B',
        ];

        return $colors[$type] ?? '#607D8B';
    }

    private function getEventIcon($type)
    {
        $icons = [
            'service' => 'mdi-church',
            'midweek' => 'mdi-calendar',
            'prayer' => 'mdi-hand-heart',
            'bible_study' => 'mdi-book-open-variant',
            'youth' => 'mdi-account-group',
            'children' => 'mdi-human-child',
            'women' => 'mdi-human-female',
            'men' => 'mdi-human-male',
            'outreach' => 'mdi-hand-heart',
            'social' => 'mdi-party-popper',
            'training' => 'mdi-school',
            'conference' => 'mdi-microphone',
            'other' => 'mdi-calendar',
        ];

        return $icons[$type] ?? 'mdi-calendar';
    }

    private function getAvatarColor($member)
    {
        $colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info', 'purple', 'pink', 'teal'];
        $name = ($member->first_name . $member->other_name . $member->last_name);
        $hash = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            $hash = ord($name[$i]) + (($hash << 5) - $hash);
        }
        $index = abs($hash) % count($colors);
        return $colors[$index];
    }


   public function update(Request $request, $id)
{
    try {
        $user = Auth::user();

        // Check if user has permission to update this church
        if ($user->church_id != $id && !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this church'
            ], 403);
        }

        $church = Church::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:churches,email,' . $church->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'pastor_name' => 'nullable|string|max:255',
            'pastor_email' => 'nullable|email|max:255',
            'about' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update church
        $church->update($request->all());

        // Also update corresponding settings if they exist
        $settingsToUpdate = [
            'church_name' => $request->name,
            'church_email' => $request->email,
            'church_phone' => $request->phone,
            'church_address' => $request->address,
            'pastor_name' => $request->pastor_name,
            'pastor_email' => $request->pastor_email,
        ];

        foreach ($settingsToUpdate as $key => $value) {
            Setting::updateOrCreate(
                [
                    'key' => $key,
                    'church_id' => $church->id
                ],
                [
                    'value' => $value ?? '',
                    'type' => $this->getSettingType($key),
                    'category' => 'church_info'
                ]
            );
        }

        // Refresh the church model to get updated data
        $church->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Church information updated successfully',
            'church' => $church, // Make sure to return the updated church
            'settings_updated' => true
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating church: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update church information: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Get setting type based on key
     */
    private function getSettingType($key)
    {
        $types = [
            'church_name' => 'text',
            'church_email' => 'email',
            'church_phone' => 'text',
            'church_address' => 'textarea',
            'pastor_name' => 'text',
            'pastor_email' => 'email',
        ];

        return $types[$key] ?? 'text';
    }

    // Get church details
    public function show($id)
    {
        try {
            $user = auth()->user();

            // Check if user belongs to this church
            if ($user->church_id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this church'
                ], 403);
            }

            $church = Church::findOrFail($id);

            return response()->json([
                'success' => true,
                'church' => $church
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Upload church logo
    public function uploadLogo(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if ($user->church_id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this church'
                ], 403);
            }

            $request->validate([
                'logo' => 'required|image|max:2048', // 2MB max
            ]);

            $church = Church::findOrFail($id);

            // Delete old logo if exists
            if ($church->logo && Storage::exists($church->logo)) {
                Storage::delete($church->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('church-logos', 'public');

            $church->update(['logo' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'path' => $path,
                'url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Remove church logo
    public function removeLogo($id)
    {
        try {
            $user = auth()->user();

            if ($user->church_id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this church'
                ], 403);
            }

            $church = Church::findOrFail($id);

            if ($church->logo && Storage::exists($church->logo)) {
                Storage::delete($church->logo);
            }

            $church->update(['logo' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Logo removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
