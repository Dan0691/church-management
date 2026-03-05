<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Add recurring event fields
            $table->boolean('recurring')->default(false)->after('image');
            $table->string('recurrence_pattern')->nullable()->after('recurring');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_pattern');

            // Add notification and tracking fields
            $table->boolean('send_notifications')->default(true)->after('recurrence_end_date');
            $table->boolean('track_attendance')->default(true)->after('send_notifications');

            // Add capacity, notes, and created_by
            $table->integer('capacity')->nullable()->after('track_attendance');
            $table->text('notes')->nullable()->after('capacity');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'recurring',
                'recurrence_pattern',
                'recurrence_end_date',
                'send_notifications',
                'track_attendance',
                'capacity',
                'notes',
                'created_by'
            ]);
        });
    }
};
