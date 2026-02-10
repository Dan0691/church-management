<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Get all settings for the current church
     */
    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->church_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No church associated with user'
                ], 403);
            }

            // Fetch all settings for the church
            $settings = Setting::where('church_id', $user->church_id)->get();

            // Group by category
            $groupedSettings = $settings->groupBy('category')->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'key' => $item->key,
                        'value' => $item->value,
                        'type' => $item->type,
                        'description' => $item->description,
                        'is_public' => $item->is_public,
                        'options' => $item->options,
                    ];
                });
            });

            // Get church info - make sure to include ALL fields
            $church = Church::find($user->church_id);

            if (!$church) {
                return response()->json([
                    'success' => false,
                    'message' => 'Church not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'settings' => $groupedSettings,
                'church' => $church, // Return the full church object
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
            ], 500);
        }
    }
    /**
     * Save a single setting
     */
    public function store11(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'category' => 'required|string',
            'type' => 'required|string'
        ]);

        try {
            $churchId = Auth::user()->church_id;

            $setting = Setting::updateOrCreate(
                [
                    'key' => $request->key,
                    'church_id' => $churchId
                ],
                [
                    'value' => $request->value,
                    'category' => $request->category,
                    'type' => $request->type,
                    'description' => $request->description ?? null,
                    'is_public' => $request->is_public ?? false,
                    'options' => $request->options ?? null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'setting' => $setting
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving setting: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save setting: ' . $e->getMessage()
            ], 500);
        }
    }

    // In SettingsController.php - Update the store method to handle both single and bulk
public function store(Request $request)
{
    try {
        // Check if it's a bulk update
        if ($request->has('settings') && is_array($request->settings)) {
            // Bulk update
            $request->validate([
                'settings' => 'required|array',
                'settings.*.key' => 'required|string',
                'settings.*.value' => 'required',
                'settings.*.category' => 'required|string',
                'settings.*.type' => 'required|string'
            ]);

            $churchId = Auth::user()->church_id;
            $savedSettings = [];

            foreach ($request->settings as $settingData) {
                $setting = Setting::updateOrCreate(
                    [
                        'key' => $settingData['key'],
                        'church_id' => $churchId
                    ],
                    [
                        'value' => $settingData['value'],
                        'category' => $settingData['category'],
                        'type' => $settingData['type'],
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? false,
                        'options' => $settingData['options'] ?? null
                    ]
                );

                $savedSettings[] = $setting;
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully',
                'settings' => $savedSettings
            ]);
        } else {
            // Single setting update
            $request->validate([
                'key' => 'required|string',
                'value' => 'required',
                'category' => 'required|string',
                'type' => 'required|string'
            ]);

            $churchId = Auth::user()->church_id;

            $setting = Setting::updateOrCreate(
                [
                    'key' => $request->key,
                    'church_id' => $churchId
                ],
                [
                    'value' => $request->value,
                    'category' => $request->category,
                    'type' => $request->type,
                    'description' => $request->description ?? null,
                    'is_public' => $request->is_public ?? false,
                    'options' => $request->options ?? null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'setting' => $setting
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Error saving setting: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to save setting: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Bulk save settings
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.category' => 'required|string',
            'settings.*.type' => 'required|string'
        ]);

        try {
            $churchId = Auth::user()->church_id;
            $savedSettings = [];

            foreach ($request->settings as $settingData) {
                $setting = Setting::updateOrCreate(
                    [
                        'key' => $settingData['key'],
                        'church_id' => $churchId
                    ],
                    [
                        'value' => $settingData['value'],
                        'category' => $settingData['category'],
                        'type' => $settingData['type'],
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? false,
                        'options' => $settingData['options'] ?? null
                    ]
                );

                $savedSettings[] = $setting;
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully',
                'settings' => $savedSettings
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk saving settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update multiple settings
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->church_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No church associated with user'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*.key' => 'required|string',
                'settings.*.value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updatedCount = 0;
            foreach ($request->settings as $settingData) {
                $setting = Setting::where('key', $settingData['key'])
                    ->where(function($query) use ($user) {
                        $query->where('church_id', $user->church_id)
                            ->orWhereNull('church_id');
                    })
                    ->first();

                if ($setting) {
                    $setting->update(['value' => $settingData['value']]);
                    $updatedCount++;
                } else {
                    // Create new setting if it doesn't exist
                    Setting::create([
                        'key' => $settingData['key'],
                        'value' => $settingData['value'],
                        'type' => $settingData['type'] ?? 'text',
                        'church_id' => $user->church_id,
                        'category' => $settingData['category'] ?? 'general'
                    ]);
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $updatedCount . ' settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a single setting
     */
    public function updateSetting(Request $request, $key)
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->church_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No church associated with user'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'value' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $setting = Setting::where('key', $key)
                ->where('church_id', $user->church_id)
                ->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found'
                ], 404);
            }

            $setting->update(['value' => $request->value]);

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
                'setting' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get settings by category
     */
    public function byCategory($category)
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->church_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No church associated with user'
                ], 403);
            }

            $settings = Setting::where('category', $category)
                ->where(function($query) use ($user) {
                    $query->where('church_id', $user->church_id)
                        ->orWhereNull('church_id');
                })
                ->get();

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to default
     */
    public function resetToDefault()
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->church_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No church associated with user'
                ], 403);
            }

            // Delete all church-specific settings
            Setting::where('church_id', $user->church_id)->delete();

            // Recreate default settings
            $settings = [
                // Church Info
                ['key' => 'church_name', 'value' => $user->church->name, 'type' => 'text', 'category' => 'church_info'],
                ['key' => 'church_email', 'value' => $user->church->email, 'type' => 'email', 'category' => 'church_info'],
                ['key' => 'church_phone', 'value' => $user->church->phone ?? '', 'type' => 'text', 'category' => 'church_info'],
                ['key' => 'church_address', 'value' => $user->church->address ?? '', 'type' => 'textarea', 'category' => 'church_info'],
                ['key' => 'pastor_name', 'value' => $user->church->pastor_name, 'type' => 'text', 'category' => 'church_info'],

                // Services
                ['key' => 'sunday_service_time', 'value' => '09:00', 'type' => 'time', 'category' => 'services'],
                ['key' => 'midweek_service_time', 'value' => '19:00', 'type' => 'time', 'category' => 'services'],
                ['key' => 'service_duration', 'value' => '120', 'type' => 'number', 'category' => 'services'],

                // General
                ['key' => 'app_name', 'value' => $user->church->name . ' Management System', 'type' => 'text', 'category' => 'general'],
                ['key' => 'currency', 'value' => 'NGN', 'type' => 'select', 'category' => 'financial'],
                ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'select', 'category' => 'general'],
                ['key' => 'time_format', 'value' => '24', 'type' => 'select', 'category' => 'general'],

                // Member settings
                ['key' => 'allow_member_registration', 'value' => 'true', 'type' => 'boolean', 'category' => 'members'],
                ['key' => 'member_auto_approval', 'value' => 'false', 'type' => 'boolean', 'category' => 'members'],

                // Notification settings
                ['key' => 'email_notifications', 'value' => 'true', 'type' => 'boolean', 'category' => 'notifications'],
                ['key' => 'event_reminder_days', 'value' => '1', 'type' => 'number', 'category' => 'notifications'],
            ];

            foreach ($settings as $setting) {
                Setting::create(array_merge($setting, ['church_id' => $user->church_id]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to default successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
