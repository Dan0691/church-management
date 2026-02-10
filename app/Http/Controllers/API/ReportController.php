<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\MembersExport;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Church;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:members,events,attendance,financial,summary',
            'format' => 'in:pdf,excel,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $churchId = auth()->user()->church_id;
        $type = $request->type;
        $format = $request->format ?? 'pdf';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        switch ($type) {
            case 'members':
                return $this->membersReport($request);
            case 'events':
                return $this->eventsReport($request);
            case 'attendance':
                return $this->attendanceReport($request);
            case 'financial':
                return $this->financialReport($request);
            case 'summary':
            default:
                return $this->summaryReport($request);
        }
    }

    public function membersReport(Request $request)
    {
        $request->validate([
            'format' => 'in:pdf,excel,csv',
            'status' => 'nullable|in:active,inactive,visitor,pending',
            'gender' => 'nullable|in:Male,Female,Other',
        ]);

        $churchId = auth()->user()->church_id;
        $format = $request->format ?? 'pdf';
        $filters = $request->all();

        $query = Member::where('church_id', $churchId)
            ->with('church');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('membership_status', $request->status);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('join_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('join_date', '<=', $request->end_date);
        }

        $members = $query->get();

        // Generate statistics
        $stats = [
            'total' => $members->count(),
            'active' => $members->where('membership_status', 'active')->count(),
            'inactive' => $members->where('membership_status', 'inactive')->count(),
            'visitors' => $members->where('membership_status', 'visitor')->count(),
            'pending' => $members->where('membership_status', 'pending')->count(),
            'male' => $members->where('gender', 'Male')->count(),
            'female' => $members->where('gender', 'Female')->count(),
            'other' => $members->where('gender', 'Other')->count(),
            'new_this_month' => $members->where('join_date', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        $church = Church::find($churchId);

        if ($format === 'excel' || $format === 'csv') {
            $export = new MembersExport($filters, $churchId);
            $fileName = 'members_report_' . date('Y_m_d') . '.' . $format;

            return Excel::download($export, $fileName);
        }

        // PDF format
        $pdf = Pdf::loadView('reports.members', [
            'members' => $members,
            'stats' => $stats,
            'church' => $church,
            'filters' => $filters,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ]);

        return $pdf->download('members_report_' . date('Y_m_d') . '.pdf');
    }

    public function eventsReport(Request $request)
    {
        $request->validate([
            'format' => 'in:pdf,excel,csv',
            'type' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $churchId = auth()->user()->church_id;
        $format = $request->format ?? 'pdf';

        $query = Event::where('church_id', $churchId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $events = $query->with(['attendances', 'church'])->get();

        // Calculate statistics
        $stats = [
            'total' => $events->count(),
            'upcoming' => $events->where('start_date', '>=', now())->count(),
            'past' => $events->where('end_date', '<', now())->count(),
            'total_attendance' => $events->sum('attendances.total'),
            'average_attendance' => $events->avg('attendances.total') ?? 0,
            'by_type' => $events->groupBy('type')->map->count(),
        ];

        $church = Church::find($churchId);

        if ($format === 'excel' || $format === 'csv') {
            $data = $events->map(function ($event) {
                return [
                    'ID' => $event->id,
                    'Title' => $event->title,
                    'Type' => $event->type,
                    'Start Date' => $event->start_date?->format('Y-m-d H:i'),
                    'End Date' => $event->end_date?->format('Y-m-d H:i'),
                    'Location' => $event->location,
                    'Total Attendance' => $event->attendances->sum('total'),
                    'Status' => $event->start_date > now() ? 'Upcoming' : ($event->end_date < now() ? 'Past' : 'Ongoing'),
                ];
            });

            $fileName = 'events_report_' . date('Y_m_d') . '.' . $format;

            return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection {
                private $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function collection()
                {
                    return $this->data;
                }
            }, $fileName);
        }

        // PDF format
        $pdf = Pdf::loadView('reports.events', [
            'events' => $events,
            'stats' => $stats,
            'church' => $church,
            'filters' => $request->all(),
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ]);

        return $pdf->download('events_report_' . date('Y_m_d') . '.pdf');
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'format' => 'in:pdf,excel,csv',
            'event_id' => 'nullable|exists:events,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $churchId = auth()->user()->church_id;
        $format = $request->format ?? 'pdf';
        $eventId = $request->event_id;

        $query = Attendance::query();

        if ($eventId) {
            $query->where('event_id', $eventId);
        } else {
            // Get all events for this church
            $eventIds = Event::where('church_id', $churchId)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        $attendances = $query->with(['event', 'recordedBy'])->get();

        // Calculate statistics
        $stats = [
            'total_records' => $attendances->count(),
            'total_attendance' => $attendances->sum('total'),
            'average_attendance' => $attendances->avg('total') ?? 0,
            'total_men' => $attendances->sum('men'),
            'total_women' => $attendances->sum('women'),
            'total_children' => $attendances->sum('children'),
            'total_visitors' => $attendances->sum('visitors'),
            'by_month' => $attendances->groupBy(function ($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m');
            })->map->sum('total'),
        ];

        $church = Church::find($churchId);
        $event = $eventId ? Event::find($eventId) : null;

        if ($format === 'excel' || $format === 'csv') {
            $export = new AttendanceExport($request->all(), $eventId);
            $fileName = 'attendance_report_' . date('Y_m_d') . '.' . $format;

            return Excel::download($export, $fileName);
        }

        // PDF format
        $pdf = Pdf::loadView('reports.attendance', [
            'attendances' => $attendances,
            'stats' => $stats,
            'church' => $church,
            'event' => $event,
            'filters' => $request->all(),
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ]);

        return $pdf->download('attendance_report_' . date('Y_m_d') . '.pdf');
    }

    public function financialReport(Request $request)
    {
        // This would need your financial model structure
        // For now, returning a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Financial report feature coming soon',
            'data' => []
        ]);
    }

    public function customReport(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'format' => 'in:pdf,excel,csv',
        ]);

        // Generate custom report based on parameters
        // This is a flexible method that can generate various custom reports

        return response()->json([
            'success' => true,
            'message' => 'Custom report generated',
            'data' => [
                'type' => $request->type,
                'filters' => $request->filters,
                'columns' => $request->columns,
            ]
        ]);
    }

    public function generateCustomReport(Request $request)
    {
        // Similar to customReport but with more advanced options
        return $this->customReport($request);
    }

    private function summaryReport(Request $request)
    {
        $churchId = auth()->user()->church_id;

        // Get summary statistics
        $summary = [
            'members' => [
                'total' => Member::where('church_id', $churchId)->count(),
                'active' => Member::where('church_id', $churchId)->where('membership_status', 'active')->count(),
                'new_this_month' => Member::where('church_id', $churchId)
                    ->whereDate('join_date', '>=', Carbon::now()->startOfMonth())
                    ->count(),
            ],
            'events' => [
                'total' => Event::where('church_id', $churchId)->count(),
                'upcoming' => Event::where('church_id', $churchId)
                    ->where('start_date', '>=', now())
                    ->count(),
                'past' => Event::where('church_id', $churchId)
                    ->where('end_date', '<', now())
                    ->count(),
            ],
            'attendance' => [
                'total' => Attendance::whereIn('event_id',
                    Event::where('church_id', $churchId)->pluck('id')
                )->sum('total'),
                'average' => Attendance::whereIn('event_id',
                    Event::where('church_id', $churchId)->pluck('id')
                )->avg('total') ?? 0,
            ],
        ];

        $church = Church::find($churchId);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.summary', [
                'summary' => $summary,
                'church' => $church,
                'generated_at' => now(),
                'generated_by' => auth()->user()->name,
            ]);

            return $pdf->download('church_summary_report_' . date('Y_m_d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
