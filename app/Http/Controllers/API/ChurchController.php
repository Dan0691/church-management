<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\Church;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ChurchController extends Controller
{

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

            return response()->json([
                'success' => true,
                'message' => 'Church information updated successfully',
                'church' => $church,
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
