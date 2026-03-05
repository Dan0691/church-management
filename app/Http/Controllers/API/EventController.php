<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{

 private function getCurrentChurchId()
    {
        // Get church_id from authenticated user
        $user = auth()->user();

        if (!$user) {
            \Log::error('getCurrentChurchId: No authenticated user');
            return null;
        }

        // If user has church_id directly
        if ($user->church_id) {
            \Log::info('getCurrentChurchId: Found church_id=' . $user->church_id);
            return $user->church_id;
        }

        // If user has church relationship
        if ($user->church) {
            \Log::info('getCurrentChurchId: Found church through relationship, id=' . $user->church->id);
            return $user->church->id;
        }

        \Log::warning('getCurrentChurchId: User ' . $user->id . ' has no church_id or church relationship');
        return null;
    }

    public function index(Request $request)
    {
         // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        // Start query with church filter
        // $query = Member::with('church')->where('church_id', $churchId);

        $query = Event::with('attendances','church')->where('church_id', $churchId);

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status')) {
            $now = now();
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'past':
                    $query->where('end_date', '<', $now);
                    break;
                case 'ongoing':
                    $query->where('start_date', '<=', $now)
                          ->where('end_date', '>=', $now);
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'start_date_desc');
        switch ($sortBy) {
            case 'start_date_asc':
                $query->orderBy('start_date');
                break;
            case 'title_asc':
                $query->orderBy('title');
                break;
            case 'title_desc':
                $query->orderByDesc('title');
                break;
            case 'attendance_desc':
                $query->withCount(['attendances as total_attendance' => function($q) {
                    $q->select(\DB::raw('SUM(men + women + children + visitors)'));
                }])->orderByDesc('total_attendance');
                break;
            case 'attendance_asc':
                $query->withCount(['attendances as total_attendance' => function($q) {
                    $q->select(\DB::raw('SUM(men + women + children + visitors)'));
                }])->orderBy('total_attendance');
                break;
            default:
                $query->orderByDesc('start_date');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $events = $query->paginate($perPage);

        // Calculate total attendance for each event
        $events->getCollection()->transform(function ($event) {
            $event->total_attendance = $event->attendances->sum(function ($attendance) {
                return ($attendance->men ?? 0) + ($attendance->women ?? 0) +
                       ($attendance->children ?? 0) + ($attendance->visitors ?? 0);
            });
            return $event;
        });

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'per_page' => $events->perPage(),
            'total' => $events->total(),
        ]);
    }

    public function store(Request $request)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string|in:Daily,Weekly,Bi-weekly,Monthly,Yearly',
            'recurrence_end_date' => 'nullable|date|after:start_date',
            'send_notifications' => 'boolean',
            'track_attendance' => 'boolean',
            'church_id' => 'nullable|exists:churches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $eventData = $request->all();
        $eventData['created_by'] = auth()->id();

        $event = Event::create($eventData);

        // If recurring, create future events
        if ($request->recurring && $request->recurrence_pattern && $request->recurrence_end_date) {
            $this->createRecurringEvents($event, $request->all());
        }

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    public function show($id)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $event = Event::with(['attendances', 'church'])->findOrFail($id);

        // Calculate total attendance
        $event->total_attendance = $event->attendances->sum(function ($attendance) {
            return ($attendance->men ?? 0) + ($attendance->women ?? 0) +
                   ($attendance->children ?? 0) + ($attendance->visitors ?? 0);
        });

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    public function update(Request $request, $id)
    {
          // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        // Only allow updating events from current church
        $event = Event::where('church_id', $churchId)->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found or you do not have permission to update this event'
            ], 404);
        }
        // $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string|in:Daily,Weekly,Bi-weekly,Monthly,Yearly',
            'recurrence_end_date' => 'nullable|date|after:start_date',
            'send_notifications' => 'boolean',
            'track_attendance' => 'boolean',
            'church_id' => 'nullable|exists:churches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update member data
        $updateData = $request->all();

        // Ensure church_id stays the same (prevent moving members between churches)
        unset($updateData['church_id']);

        $event->update($updateData);


        // $event->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    public function destroy($id)
    {
      // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        $event = Event::findOrFail($id);

        // Delete associated attendance records
        $event->attendances()->delete();

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    public function attend(Request $request, $id)
    {
          // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'men' => 'required|integer|min:0',
            'women' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'visitors' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $attendanceData = $request->all();
        $attendanceData['event_id'] = $id;
        $attendanceData['recorded_by'] = auth()->id();
        $attendanceData['total'] = $request->men + $request->women + $request->children + $request->visitors;

        $attendance = Attendance::create($attendanceData);

        // Update event's total attendance
        $event->touch();

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
        ]);
    }

    public function stats()
    {
          // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        $total = Event::count();
        $upcoming = Event::where('start_date', '>', now())->count();
        $past = Event::where('end_date', '<', now())->count();
        $ongoing = Event::where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->count();

        // Calculate average attendance
        $eventsWithAttendance = Event::has('attendances')->with('attendances')->get();
        $totalAttendance = 0;
        $eventCount = 0;

        foreach ($eventsWithAttendance as $event) {
            $eventAttendance = $event->attendances->sum(function ($attendance) {
                return ($attendance->men ?? 0) + ($attendance->women ?? 0) +
                       ($attendance->children ?? 0) + ($attendance->visitors ?? 0);
            });
            if ($eventAttendance > 0) {
                $totalAttendance += $eventAttendance;
                $eventCount++;
            }
        }

        $averageAttendance = $eventCount > 0 ? round($totalAttendance / $eventCount) : 0;

        // This month events
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        $thisMonthEvents = Event::whereBetween('start_date', [$thisMonthStart, $thisMonthEnd])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'upcoming' => $upcoming,
                'past' => $past,
                'ongoing' => $ongoing,
                'average_attendance' => $averageAttendance,
                'this_month' => $thisMonthEvents,
                'total_attendance' => $totalAttendance,
            ]
        ]);
    }

    public function upcoming()
    {
          // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        $events = Event::with('attendances')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Calculate total attendance for each event
        $events->transform(function ($event) {
            $event->total_attendance = $event->attendances->sum(function ($attendance) {
                return ($attendance->men ?? 0) + ($attendance->women ?? 0) +
                       ($attendance->children ?? 0) + ($attendance->visitors ?? 0);
            });
            return $event;
        });

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    public function categories()
    {
          // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with event'
            ], 403);
        }

        $categories = Event::distinct('category')
            ->whereNotNull('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function calendar(Request $request)
    {

        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = date("{$year}-{$month}-01");
        $endDate = date("{$year}-{$month}-t", strtotime($startDate));

        $events = Event::whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->get();

        $calendarEvents = $events->map(function ($event) {
            return [
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'color' => $this->getEventColor($event->type),
                'allDay' => false,
                'extendedProps' => [
                    'type' => $event->type,
                    'location' => $event->location,
                    'description' => $event->description,
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $calendarEvents
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

    private function createRecurringEvents($originalEvent, $data)
    {
        $pattern = $data['recurrence_pattern'];
        $endDate = $data['recurrence_end_date'];
        $currentDate = $originalEvent->start_date;

        $interval = 1;
        if ($pattern === 'Bi-weekly') {
            $pattern = 'Weekly';
            $interval = 2;
        }

        while ($currentDate < $endDate) {
            // Increment date based on pattern
            switch ($pattern) {
                case 'Daily':
                    $currentDate = date('Y-m-d H:i:s', strtotime($currentDate . ' +' . $interval . ' day'));
                    break;
                case 'Weekly':
                    $currentDate = date('Y-m-d H:i:s', strtotime($currentDate . ' +' . $interval . ' week'));
                    break;
                case 'Monthly':
                    $currentDate = date('Y-m-d H:i:s', strtotime($currentDate . ' +' . $interval . ' month'));
                    break;
                case 'Yearly':
                    $currentDate = date('Y-m-d H:i:s', strtotime($currentDate . ' +' . $interval . ' year'));
                    break;
            }

            if ($currentDate <= $endDate) {
                // Create new event
                $newEventData = $data;
                $newEventData['start_date'] = $currentDate;
                $newEventData['end_date'] = date('Y-m-d H:i:s', strtotime($currentDate . ' +' .
                    strtotime($originalEvent->end_date) - strtotime($originalEvent->start_date) . ' seconds'));
                $newEventData['recurring'] = false; // Don't make the child events recurring
                $newEventData['parent_event_id'] = $originalEvent->id;
                $newEventData['created_by'] = auth()->id();

                Event::create($newEventData);
            }
        }
    }

    // Add other methods as needed...
}
