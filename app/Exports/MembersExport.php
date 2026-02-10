<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MembersExport implements FromCollection, WithHeadings, WithMapping
{
    private $churchId;
    private $isTemplate;

    public function __construct($churchId = null, $isTemplate = false)
    {
        $this->churchId = $churchId;
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            // Return empty collection for template
            return collect([]);
        }

        if ($this->churchId) {
            return Member::with('church')
                ->where('church_id', $this->churchId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Fallback: return all members (should not happen with proper auth)
        return Member::with('church')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        if ($this->isTemplate) {
            return [
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Birth Date (YYYY-MM-DD)',
                'Join Date (YYYY-MM-DD)',
                'Gender',
                'Marital Status',
                'Occupation',
                'Address',
                'City',
                'State',
                'ZIP Code',
                'Membership Status',
                'Notes'
            ];
        }

        return [
            // 'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Gender',
            'Marital Status',
            'Occupation',
            'Birth Date',
            'Age',
            'Join Date',
            'Membership Duration',
            'Membership Status',
            'Address',
            'City',
            'State',
            'ZIP Code',
            'Notes',
            // 'Created By',
            // 'Created At',
            // 'Updated At'
        ];
    }

    public function map($member): array
    {
        if ($this->isTemplate) {
            return []; // Empty for template
        }

        return [
            // $member->id,
            $member->first_name,
            $member->last_name,
            $member->email ?? '',
            $member->phone ?? '',
            $member->gender ?? '',
            $member->marital_status ?? '',
            $member->occupation ?? '',
            $member->birth_date ?? '',
            $this->calculateAge($member->birth_date),
            $member->join_date,
            $this->calculateDuration($member->join_date),
            $member->membership_status,
            $member->address ?? '',
            $member->city ?? '',
            $member->state ?? '',
            $member->zip_code ?? '',
            $member->notes ?? ''
            // $member->created_by ?? '',
            // $member->created_at,
            // $member->updated_at
        ];
    }

    private function calculateAge($birthDate)
    {
        if (!$birthDate) return '';

        try {
            $today = new \DateTime();
            $birth = new \DateTime($birthDate);
            $age = $today->diff($birth)->y;
            return $age;
        } catch (\Exception $e) {
            return '';
        }
    }

    private function calculateDuration($joinDate)
    {
        if (!$joinDate) return '';

        try {
            $today = new \DateTime();
            $join = new \DateTime($joinDate);
            $diff = $today->diff($join);

            if ($diff->y > 0) {
                return $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
            } elseif ($diff->m > 0) {
                return $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
            } else {
                return 'Less than a month';
            }
        } catch (\Exception $e) {
            return '';
        }
    }
}
