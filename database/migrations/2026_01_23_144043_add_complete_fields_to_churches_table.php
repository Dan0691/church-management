<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('churches', function (Blueprint $table) {
            // Add city if it doesn't exist
            if (!Schema::hasColumn('churches', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            // Add state if it doesn't exist
            if (!Schema::hasColumn('churches', 'state')) {
                $table->string('state')->nullable()->after('city');
            }

            // Add country if it doesn't exist
            if (!Schema::hasColumn('churches', 'country')) {
                $table->string('country')->nullable()->after('state');
            }

            // Add postal_code if it doesn't exist
            if (!Schema::hasColumn('churches', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }

            // Add pastor_phone if it doesn't exist
            if (!Schema::hasColumn('churches', 'pastor_phone')) {
                $table->string('pastor_phone')->nullable()->after('pastor_name');
            }

            // Add pastor_email if it doesn't exist
            if (!Schema::hasColumn('churches', 'pastor_email')) {
                $table->string('pastor_email')->nullable()->after('pastor_phone');
            }

            // Add website if it doesn't exist
            if (!Schema::hasColumn('churches', 'website')) {
                $table->string('website')->nullable()->after('pastor_email');
            }

            // Add logo if it doesn't exist
            if (!Schema::hasColumn('churches', 'logo')) {
                $table->string('logo')->nullable()->after('website');
            }

            // Add banner_image if it doesn't exist
            if (!Schema::hasColumn('churches', 'banner_image')) {
                $table->string('banner_image')->nullable()->after('logo');
            }

            // Add denomination if it doesn't exist
            if (!Schema::hasColumn('churches', 'denomination')) {
                $table->string('denomination')->nullable()->after('banner_image');
            }

            // Add established_date if it doesn't exist
            if (!Schema::hasColumn('churches', 'established_date')) {
                $table->date('established_date')->nullable()->after('denomination');
            }

            // Add membership_count if it doesn't exist
            if (!Schema::hasColumn('churches', 'membership_count')) {
                $table->integer('membership_count')->default(0)->after('established_date');
            }

            // Add service_times if it doesn't exist
            if (!Schema::hasColumn('churches', 'service_times')) {
                $table->json('service_times')->nullable()->after('membership_count');
            }

            // Add about if it doesn't exist
            if (!Schema::hasColumn('churches', 'about')) {
                $table->text('about')->nullable()->after('service_times');
            }

            // Add mission_statement if it doesn't exist
            if (!Schema::hasColumn('churches', 'mission_statement')) {
                $table->text('mission_statement')->nullable()->after('about');
            }

            // Add vision_statement if it doesn't exist
            if (!Schema::hasColumn('churches', 'vision_statement')) {
                $table->text('vision_statement')->nullable()->after('mission_statement');
            }

            // Add core_values if it doesn't exist
            if (!Schema::hasColumn('churches', 'core_values')) {
                $table->json('core_values')->nullable()->after('vision_statement');
            }

            // Add social_media if it doesn't exist
            if (!Schema::hasColumn('churches', 'social_media')) {
                $table->json('social_media')->nullable()->after('core_values');
            }

            // Add bank_name if it doesn't exist
            if (!Schema::hasColumn('churches', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('social_media');
            }

            // Add account_number if it doesn't exist
            if (!Schema::hasColumn('churches', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_name');
            }

            // Add account_name if it doesn't exist
            if (!Schema::hasColumn('churches', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }

            // Add swift_code if it doesn't exist
            if (!Schema::hasColumn('churches', 'swift_code')) {
                $table->string('swift_code')->nullable()->after('account_name');
            }

            // Add is_active if it doesn't exist
            if (!Schema::hasColumn('churches', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('swift_code');
            }

            // Add settings if it doesn't exist
            if (!Schema::hasColumn('churches', 'settings')) {
                $table->json('settings')->nullable()->after('is_active');
            }

            // Add metadata if it doesn't exist
            if (!Schema::hasColumn('churches', 'metadata')) {
                $table->json('metadata')->nullable()->after('settings');
            }
        });
    }

    public function down()
    {
        Schema::table('churches', function (Blueprint $table) {
            // Drop the columns in reverse order
            $table->dropColumn([
                'metadata',
                'settings',
                'is_active',
                'swift_code',
                'account_name',
                'account_number',
                'bank_name',
                'social_media',
                'core_values',
                'vision_statement',
                'mission_statement',
                'about',
                'service_times',
                'membership_count',
                'established_date',
                'denomination',
                'banner_image',
                'logo',
                'website',
                'pastor_email',
                'pastor_phone',
                'postal_code',
                'country',
                'state',
                'city'
            ]);
        });
    }
};