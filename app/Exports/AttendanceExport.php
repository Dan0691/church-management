<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;
    protected $eventId;

    public function __construct($filters = [], $eventId = null)
    {
        $this->filters = $filters;
        $this->eventId = $eventId;
    }

    public function collection()
    {
        $query = Attendance::query();

        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }

        // Apply filters
        if (isset($this->filters['start_date']) && $this->filters['start_date']) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date']) && $this->filters['end_date']) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->with(['event', 'recordedBy'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Event',
            'Date',
            'Men',
            'Women',
            'Children',
            'Visitors',
            'Total',
            'Notes',
            'Recorded By',
            'Created At'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->event?->title,
            $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : '',
            $attendance->men,
            $attendance->women,
            $attendance->children,
            $attendance->visitors,
            $attendance->total,
            $attendance->notes,
            $attendance->recordedBy?->name,
            $attendance->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A:Z' => ['alignment' => ['vertical' => 'center']],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 25,  // Event
            'C' => 15,  // Date
            'D' => 10,  // Men
            'E' => 10,  // Women
            'F' => 10,  // Children
            'G' => 10,  // Visitors
            'H' => 10,  // Total
            'I' => 30,  // Notes
            'J' => 20,  // Recorded By
            'K' => 20,  // Created At
        ];
    }
}
