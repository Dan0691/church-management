<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\User;
use App\Models\Church;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;
            $church = $user->church;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'church' => $church,
                'message' => 'Login successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }


    public function register(Request $request)
    {
        try {
            $request->validate([
                "church_name" => ["required", "string", "max:255", "unique:churches,name"],
                "church_email" => ["required", "string", "email", "max:255", "unique:churches,email"],
                "pastor_name" => ["required", "string", "max:255"],
                "name" => ["required", "string", "max:255"],
                "email" => ["required", "string", "email", "max:255", "unique:users,email"],
                "phone" => ["required", "string", "max:20"],
                "password" => ["required", "confirmed", "min:8"],
            ]);

            // Create church
            $church = Church::create([
                "name" => $request->church_name,
                "slug" => Str::slug($request->church_name),
                "email" => $request->church_email,
                "pastor_name" => $request->pastor_name,
                "is_active" => true,
            ]);

            // Create admin user
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "phone" => $request->phone,
                "password" => Hash::make($request->password),
                "church_id" => $church->id,
                "role" => "admin",
                "is_active" => true,
            ]);

            // Create default settings for the church
            $this->createChurchSettings($church);

            // Create Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'church' => $church,
                'message' => 'Registration successful'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createChurchSettings($church)
    {
        $settings = [
            // Church Info
            ['key' => 'church_name', 'value' => $church->name, 'type' => 'text', 'category' => 'church_info', 'church_id' => $church->id],
            ['key' => 'church_email', 'value' => $church->email, 'type' => 'email', 'category' => 'church_info', 'church_id' => $church->id],
            ['key' => 'church_phone', 'value' => $church->phone ?? '', 'type' => 'text', 'category' => 'church_info', 'church_id' => $church->id],
            ['key' => 'church_address', 'value' => $church->address ?? '', 'type' => 'textarea', 'category' => 'church_info', 'church_id' => $church->id],
            ['key' => 'pastor_name', 'value' => $church->pastor_name, 'type' => 'text', 'category' => 'church_info', 'church_id' => $church->id],

            // Services
            ['key' => 'sunday_service_time', 'value' => '09:00', 'type' => 'time', 'category' => 'services', 'church_id' => $church->id],
            ['key' => 'midweek_service_time', 'value' => '19:00', 'type' => 'time', 'category' => 'services', 'church_id' => $church->id],

            // General
            ['key' => 'app_name', 'value' => $church->name . ' Management System', 'type' => 'text', 'category' => 'general', 'church_id' => $church->id],
            ['key' => 'currency', 'value' => 'NGN', 'type' => 'select', 'category' => 'financial', 'church_id' => $church->id],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();
            $church = $user->church;

            return response()->json([
                'success' => true,
                'user' => $user,
                'church' => $church
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot password.
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            // Here you would send password reset email
            // For now, just return success message

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:8',
            ]);

            // Here you would validate token and reset password
            // For now, just return success message

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto(Request $request)
    {
        \Log::info('Upload profile photo request received');
        \Log::info('User:', ['user_id' => $request->user()->id]);

        try {
            $request->validate([
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $user = $request->user();
            \Log::info('Current user profile photo:', ['current_photo' => $user->profile_photo]);

            // Delete old photo if exists
            if ($user->profile_photo && Storage::exists($user->profile_photo)) {
                Storage::delete($user->profile_photo);
                \Log::info('Old photo deleted');
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            \Log::info('New photo stored at:', ['path' => $path]);

            $user->profile_photo = $path;
            $user->save();

            \Log::info('User profile photo updated in database');

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully',
                'path' => $path,
                'url' => Storage::url($path),
                'user' => $user->fresh() // Return fresh user data with accessors
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile photo upload error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove profile photo
     */
    public function removeProfilePhoto(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->profile_photo && Storage::exists($user->profile_photo)) {
                Storage::delete($user->profile_photo);
            }

            $user->profile_photo = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully',
                'user' => $user->fresh() // Return updated user data
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile photo removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove profile photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:1000',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:Male,Female,Other',
                'marital_status' => 'nullable|string|in:Single,Married,Divorced,Widowed,Separated',
                'occupation' => 'nullable|string|max:255',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
            ]);

            $user->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Update profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|confirmed|min:8',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
