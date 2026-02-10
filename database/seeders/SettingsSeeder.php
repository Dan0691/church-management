<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing settings first
        Setting::truncate();

        // Get the first church or default church_id
        $defaultChurchId = 1; // Or get from your churches table

        // If you want church-specific settings (recommended):
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'Church Management System',
                'type' => 'text',
                'description' => 'Application name displayed throughout the system',
                'category' => 'general',
                'is_public' => true,
                'church_id' => $defaultChurchId,
                'options' => null,
            ],
            [
                'key' => 'app_timezone',
                'value' => 'UTC',
                'type' => 'select',
                'description' => 'Default timezone for the application',
                'category' => 'general',
                'is_public' => true,
                'church_id' => $defaultChurchId,
                'options' => json_encode(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Australia/Sydney', 'Nigeria/Lagos']),
            ],
            [
                'key' => 'app_locale',
                'value' => 'en',
                'type' => 'select',
                'description' => 'Default language for the application',
                'category' => 'general',
                'is_public' => true,
                'church_id' => $defaultChurchId,
                'options' => json_encode(['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German']),
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'select',
                'description' => 'Default date format',
                'category' => 'general',
                'is_public' => true,
                'church_id' => $defaultChurchId,
                'options' => json_encode(['Y-m-d' => 'YYYY-MM-DD', 'd/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY', 'd-M-Y' => 'DD-MMM-YYYY']),
            ],
            [
                'key' => 'time_format',
                'value' => '24',
                'type' => 'select',
                'description' => 'Default time format',
                'category' => 'general',
                'is_public' => true,
                'church_id' => $defaultChurchId,
                'options' => json_encode(['24' => '24 hour', '12' => '12 hour']),
            ],

            // Church Settings Template
            [
                'key' => 'church_name',
                'value' => '',
                'type' => 'text',
                'description' => 'Official name of the church',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'church_email',
                'value' => '',
                'type' => 'email',
                'description' => 'Primary email address for the church',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'church_phone',
                'value' => '',
                'type' => 'text',
                'description' => 'Primary phone number for the church',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'church_address',
                'value' => '',
                'type' => 'textarea',
                'description' => 'Physical address of the church',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'church_city',
                'value' => '',
                'type' => 'text',
                'description' => 'City where the church is located',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'church_country',
                'value' => '',
                'type' => 'text',
                'description' => 'Country where the church is located',
                'category' => 'church_info',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Services Settings
            [
                'key' => 'sunday_service_time',
                'value' => '10:00',
                'type' => 'time',
                'description' => 'Sunday main service time',
                'category' => 'services',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'midweek_service_time',
                'value' => '19:00',
                'type' => 'time',
                'description' => 'Midweek service time',
                'category' => 'services',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'service_duration',
                'value' => '120',
                'type' => 'number',
                'description' => 'Average service duration in minutes',
                'category' => 'services',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Member Settings
            [
                'key' => 'member_auto_approval',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Automatically approve new members',
                'category' => 'members',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'allow_member_registration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Allow members to register themselves',
                'category' => 'members',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'require_member_approval',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Require admin approval for new members',
                'category' => 'members',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Financial Settings
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'select',
                'description' => 'Default currency for financial transactions',
                'category' => 'financial',
                'options' => json_encode(['USD' => 'US Dollar', 'EUR' => 'Euro', 'GBP' => 'British Pound', 'NGN' => 'Nigerian Naira']), // Fixed: needs json_encode
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'number',
                'description' => 'Tax rate percentage',
                'category' => 'financial',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'donation_receipt_auto',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Automatically generate donation receipts',
                'category' => 'financial',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Notification Settings
            [
                'key' => 'email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'category' => 'notifications',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'sms_notifications',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable SMS notifications',
                'category' => 'notifications',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'event_reminder_days',
                'value' => '1',
                'type' => 'number',
                'description' => 'Days before event to send reminder',
                'category' => 'notifications',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Security Settings
            [
                'key' => 'session_timeout',
                'value' => '30',
                'type' => 'number',
                'description' => 'Session timeout in minutes',
                'category' => 'security',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'password_expiry_days',
                'value' => '90',
                'type' => 'number',
                'description' => 'Password expiry in days',
                'category' => 'security',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'two_factor_auth',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable two-factor authentication',
                'category' => 'security',
                'is_public' => false,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],

            // Appearance Settings
            [
                'key' => 'theme_color',
                'value' => 'primary',
                'type' => 'select',
                'description' => 'Primary theme color',
                'category' => 'appearance',
                'options' => json_encode(['primary' => 'Blue', 'secondary' => 'Gray', 'success' => 'Green', 'error' => 'Red', 'warning' => 'Orange', 'info' => 'Teal']), // Fixed: needs json_encode
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
            ],
            [
                'key' => 'dark_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable dark mode',
                'category' => 'appearance',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
            [
                'key' => 'show_church_logo',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Display church logo in header',
                'category' => 'appearance',
                'is_public' => true,
                'church_id' => $defaultChurchId, // Added missing church_id
                'options' => null, // Added missing options
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'church_id' => $setting['church_id']
                ],
                $setting
            );
        }

        // Create church-specific settings for existing churches
        // Only run this if you have churches table and Church model
        if (class_exists(\App\Models\Church::class)) {
            $churches = \App\Models\Church::all();

            foreach ($churches as $church) {
                $churchSettings = [
                    [
                        'key' => 'church_name',
                        'value' => $church->name,
                        'type' => 'text',
                        'description' => 'Official name of the church',
                        'category' => 'church_info',
                        'is_public' => true,
                        'church_id' => $church->id,
                        'options' => null,
                    ],
                    [
                        'key' => 'church_email',
                        'value' => $church->email,
                        'type' => 'email',
                        'description' => 'Primary email address for the church',
                        'category' => 'church_info',
                        'is_public' => true,
                        'church_id' => $church->id,
                        'options' => null,
                    ],
                    [
                        'key' => 'church_phone',
                        'value' => $church->phone,
                        'type' => 'text',
                        'description' => 'Primary phone number for the church',
                        'category' => 'church_info',
                        'is_public' => true,
                        'church_id' => $church->id,
                        'options' => null,
                    ],
                    [
                        'key' => 'church_address',
                        'value' => $church->address,
                        'type' => 'textarea',
                        'description' => 'Physical address of the church',
                        'category' => 'church_info',
                        'is_public' => true,
                        'church_id' => $church->id,
                        'options' => null,
                    ],
                ];

                foreach ($churchSettings as $setting) {
                    Setting::updateOrCreate(
                        [
                            'key' => $setting['key'],
                            'church_id' => $setting['church_id']
                        ],
                        $setting
                    );
                }
            }
        }
    }
}
