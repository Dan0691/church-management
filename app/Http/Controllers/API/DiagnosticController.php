<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    public function checkAuth(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'authenticated' => !!$user,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_church_id' => $user?->church_id,
            'user_role' => $user?->role,
            'token' => $request->bearerToken() ? 'Present' : 'Missing',
        ]);
    }

    public function checkMember(Request $request, $id)
    {
        $user = auth()->user();
        $churchId = $user?->church_id;

        if (!$churchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No church_id in authenticated user',
                'user' => [
                    'id' => $user?->id,
                    'church_id' => $user?->church_id,
                ]
            ]);
        }

        $member = \App\Models\Member::find($id);

        return response()->json([
            'status' => 'ok',
            'user_church_id' => $churchId,
            'member_church_id' => $member?->church_id,
            'member_exists' => !!$member,
            'church_match' => $member?->church_id == $churchId,
            'member' => $member,
        ]);
    }
}
