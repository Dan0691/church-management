<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profile columns
            $table->string('profile_photo')->nullable()->after('password');
            $table->text('address')->nullable()->after('profile_photo');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->text('bio')->nullable()->after('postal_code');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('emergency_contact_name')->nullable()->after('occupation');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');

            // Status columns
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Settings as JSON
            $table->json('settings')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'profile_photo',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'bio',
                'date_of_birth',
                'gender',
                'marital_status',
                'occupation',
                'emergency_contact_name',
                'emergency_contact_phone',
                'is_active',
                'last_login_at',
                'settings'
            ]);
        });
    }
};
