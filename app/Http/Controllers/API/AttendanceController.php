<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{

/**
 * Get all attendance records with filters
 */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Attendance::with(['event', 'recorder'])
                ->whereHas('event', function ($q) use ($user) {
                    $q->where('church_id', $user->church_id);
                });

            // Apply filters
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('event', function ($q2) use ($search) {
                        $q2->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            if ($request->has('event_id') && $request->event_id) {
                $query->where('event_id', $request->event_id);
            }

            if ($request->has('category') && $request->category) {
                $query->whereHas('event', function ($q) use ($request) {
                    $q->where('type', $request->category);
                });
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Sort by latest first
            $query->orderBy('created_at', 'desc');

            $attendances = $query->get();

            return response()->json([
                'success' => true,
                'data' => $attendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance records'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
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
        $attendanceData['recorded_by'] = auth()->id();
        $attendanceData['total'] = $request->men + $request->women + $request->children + $request->visitors;

        $attendance = Attendance::create($attendanceData);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
        ]);
    }

    public function show($id)
    {
        
        $attendance = Attendance::with(['event', 'recorder'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $attendance = Attendance::findOrFail($id);

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

        $updateData = $request->all();
        $updateData['total'] = $request->men + $request->women + $request->children + $request->visitors;

        $attendance->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Delete an attendance record
     */
    public function destroy($id)
    {
        $user = auth()->user();

        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    public function getByEvent($event_id)
    {
        
        $attendances = Attendance::with('recorder')
            ->where('event_id', $event_id)
            ->orderByDesc('created_at')
            ->get();

        $event = Event::find($event_id);

        // Calculate totals
        $totals = [
            'men' => $attendances->sum('men'),
            'women' => $attendances->sum('women'),
            'children' => $attendances->sum('children'),
            'visitors' => $attendances->sum('visitors'),
            'total' => $attendances->sum('total'),
            'records' => $attendances->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
                'attendances' => $attendances,
                'totals' => $totals,
            ]
        ]);
    }

    public function eventStats($event_id)
    {
        $attendances = Attendance::where('event_id', $event_id)->get();

        $stats = [
            'total_records' => $attendances->count(),
            'total_attendance' => $attendances->sum('total'),
            'average_attendance' => $attendances->count() > 0 ? round($attendances->sum('total') / $attendances->count()) : 0,
            'men_total' => $attendances->sum('men'),
            'women_total' => $attendances->sum('women'),
            'children_total' => $attendances->sum('children'),
            'visitors_total' => $attendances->sum('visitors'),
            'max_attendance' => $attendances->max('total'),
            'min_attendance' => $attendances->min('total'),
            'latest_record' => $attendances->sortByDesc('created_at')->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function stats()
    {
        $totalAttendance = Attendance::sum('total');
        $totalRecords = Attendance::count();
        $averageAttendance = $totalRecords > 0 ? round($totalAttendance / $totalRecords) : 0;

        // This month
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        $thisMonthAttendance = Attendance::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->sum('total');
        $thisMonthRecords = Attendance::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();

        // Last month
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthAttendance = Attendance::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total');
        $lastMonthRecords = Attendance::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $growth = $lastMonthAttendance > 0 ?
            (($thisMonthAttendance - $lastMonthAttendance) / $lastMonthAttendance) * 100 : 100;

        return response()->json([
            'success' => true,
            'data' => [
                'total_attendance' => $totalAttendance,
                'total_records' => $totalRecords,
                'average_per_record' => $averageAttendance,
                'this_month' => [
                    'attendance' => $thisMonthAttendance,
                    'records' => $thisMonthRecords,
                    'average' => $thisMonthRecords > 0 ? round($thisMonthAttendance / $thisMonthRecords) : 0,
                ],
                'last_month' => [
                    'attendance' => $lastMonthAttendance,
                    'records' => $lastMonthRecords,
                    'average' => $lastMonthRecords > 0 ? round($lastMonthAttendance / $lastMonthRecords) : 0,
                ],
                'growth_percentage' => round($growth, 2),
            ]
        ]);
    }

    public function trends(Request $request)
    {
        $period = $request->get('period', 'month');
        $data = [];

        if ($period === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $attendance = Attendance::whereDate('created_at', $date)->sum('total');
                $records = Attendance::whereDate('created_at', $date)->count();
                $data[] = [
                    'date' => $date,
                    'attendance' => $attendance,
                    'records' => $records,
                    'average' => $records > 0 ? round($attendance / $records) : 0,
                    'day' => now()->subDays($i)->format('D'),
                ];
            }
        } else {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('Y-m');
                $attendance = Attendance::whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->sum('total');
                $records = Attendance::whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count();
                $data[] = [
                    'date' => $month,
                    'attendance' => $attendance,
                    'records' => $records,
                    'average' => $records > 0 ? round($attendance / $records) : 0,
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

    public function monthly($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $attendances = Attendance::with('event')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('created_at')
            ->get();

        // Group by day
        $dailyData = [];
        for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayAttendance = $attendances->filter(function ($attendance) use ($date) {
                return $attendance->created_at->format('Y-m-d') === $date;
            });

            $dailyData[] = [
                'date' => $date,
                'attendance' => $dayAttendance->sum('total'),
                'records' => $dayAttendance->count(),
                'events' => $dayAttendance->unique('event_id')->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'total_attendance' => $attendances->sum('total'),
                'total_records' => $attendances->count(),
                'daily_data' => $dailyData,
                'attendances' => $attendances,
            ]
        ]);
    }

    public function exportEventAttendance99($event_id)
    {
        $event = Event::findOrFail($event_id);
        $filename = 'attendance_' . str_replace(' ', '_', $event->title) . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new AttendanceExport($event_id), $filename);
    }
    /**
 * Export attendance data
 */
    public function exportEventAttendance(Request $request)
    {
        try {
            $user = auth()->user();

            // allow optional filters (date range, event, category)
            $filters = $request->only(['start_date', 'end_date', 'event_id', 'category']);

            $filename = 'attendance_export_' . date('Y-m-d') . '.xlsx';

            // the export class will apply filters and include church constraint
            return Excel::download(new AttendanceExport($filters), $filename);
        } catch (\Exception $e) {
            Log::error('Error exporting attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export attendance data'
            ], 500);
        }
    }

    // Add other methods as needed...
}
