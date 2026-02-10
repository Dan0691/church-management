<?php

namespace App\Imports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class MembersImport extends DefaultValueBinder implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithCustomValueBinder,
    WithCalculatedFormulas
{
    protected $churchId;

    public function __construct($churchId)
    {
        $this->churchId = $churchId;
    }

    /**
     * Bind value to a cell - force specific columns to be strings
     */
    public function bindValue(Cell $cell, $value)
    {
        // Force phone, zip_code to be treated as strings
        $stringColumns = ['phone', 'zip_code'];

        $column = $cell->getColumn();

        // Track headers
        static $headers = [];
        static $headerRow = 1;

        if ($cell->getRow() === $headerRow) {
            $headers[$column] = strtolower($value);
        }

        // If this is a string column and value is numeric, force string
        if (isset($headers[$column]) &&
            in_array($headers[$column], $stringColumns) &&
            is_numeric($value)) {

            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Convert Excel serial date to Y-m-d format
     */
    private function excelToDate($excelDate)
    {
        if (is_numeric($excelDate)) {
            // Excel serial date (Windows variant)
            $unixDate = ($excelDate - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }

        return $excelDate;
    }

    /**
     * Process each row
     */
    public function model(array $row)
    {
        // Clean phone number
        $phone = isset($row['phone']) ? $this->cleanPhone($row['phone']) : null;

        // Convert dates
        $birthDate = isset($row['birth_date']) ? $this->parseDate($row['birth_date']) : null;
        $joinDate = isset($row['join_date']) ? $this->parseDate($row['join_date']) : null;

        // Validate join date is not in future
        if ($joinDate && strtotime($joinDate) > time()) {
            throw new \Exception("Join date cannot be in the future: {$row['join_date']}");
        }

        return new Member([
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $phone,
            'birth_date' => $birthDate,
            'join_date' => $joinDate,
            'gender' => isset($row['gender']) ? ucfirst(strtolower($row['gender'])) : null,
            'marital_status' => isset($row['marital_status']) ? ucfirst(strtolower($row['marital_status'])) : null,
            'occupation' => $row['occupation'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'zip_code' => isset($row['zip_code']) ? (string)$row['zip_code'] : null,
            'membership_status' => isset($row['membership_status']) ? strtolower($row['membership_status']) : 'visitor',
            'notes' => $row['notes'] ?? null,
            'church_id' => $this->churchId,
        ]);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // If it's an Excel serial number
        if (is_numeric($date)) {
            $unixDate = ($date - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }

        // If it's already a date string
        try {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Clean phone number
     */
    private function cleanPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Convert to string
        $phone = (string)$phone;

        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Limit to 15 digits
        $phone = substr($phone, 0, 15);

        return !empty($phone) ? $phone : null;
    }

    /**
     * Custom validation rules
     */
    public function rules(): array
    {
        return [
            '*.first_name' => 'required|string|max:255',
            '*.last_name' => 'required|string|max:255',
            '*.email' => 'nullable|email|max:255',
            '*.phone' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Accept both string and numeric values
                    if (!is_string($value) && !is_numeric($value) && $value !== null) {
                        $fail('The phone must be a string or number.');
                    }
                }
            ],
            '*.birth_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Accept Excel serial dates and date strings
                    if (!empty($value)) {
                        try {
                            if (is_numeric($value)) {
                                // It's an Excel serial date - validate range
                                if ($value < 0 || $value > 99999) {
                                    $fail('Invalid date format.');
                                }
                            } else {
                                // Try to parse as date
                                $timestamp = strtotime($value);
                                if ($timestamp === false) {
                                    $fail('Invalid date format.');
                                }
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid date format.');
                        }
                    }
                }
            ],
            '*.join_date' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        try {
                            $date = null;
                            if (is_numeric($value)) {
                                // Excel serial date
                                $unixDate = ($value - 25569) * 86400;
                                $date = date('Y-m-d', $unixDate);
                            } else {
                                $timestamp = strtotime($value);
                                if ($timestamp !== false) {
                                    $date = date('Y-m-d', $timestamp);
                                }
                            }

                            if (!$date) {
                                $fail('Invalid join date format.');
                            } elseif (strtotime($date) > time()) {
                                $fail('Join date cannot be in the future.');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid join date format.');
                        }
                    } else {
                        $fail('Join date is required.');
                    }
                }
            ],
            '*.gender' => 'nullable|in:Male,Female,Other',
            '*.marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated',
            '*.occupation' => 'nullable|string|max:255',
            '*.address' => 'nullable|string|max:500',
            '*.city' => 'nullable|string|max:100',
            '*.state' => 'nullable|string|max:100',
            '*.zip_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Accept both string and numeric
                    if (!is_string($value) && !is_numeric($value) && $value !== null) {
                        $fail('The zip code must be a string or number.');
                    }
                }
            ],
            '*.membership_status' => 'required|in:active,inactive,visitor,pending',
            '*.notes' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            '*.first_name.required' => 'First name is required',
            '*.last_name.required' => 'Last name is required',
            '*.join_date.required' => 'Join date is required',
            '*.membership_status.required' => 'Membership status is required',
            '*.membership_status.in' => 'Membership status must be: active, inactive, visitor, or pending',
        ];
    }
}
