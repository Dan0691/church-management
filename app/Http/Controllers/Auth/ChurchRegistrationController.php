<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChurchRegistrationController extends Controller
{
    // Show registration form
    public function create()
    {
        return view("auth.church-register");
    }

    // Handle registration
    public function store(Request $request)
    {
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
        ]);

        // Create admin user
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "phone" => $request->phone,
            "password" => Hash::make($request->password),
            "church_id" => $church->id,
            "role" => "admin",
        ]);

        // Log in the user
        auth()->login($user);

        // Redirect to Vue.js app
        return redirect("/app");
    }
}
