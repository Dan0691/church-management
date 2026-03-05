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
            $table->enum('type', [
                'service',
                'meeting',
                'outreach',
                'social',
                'youth',
                'children',
                'women',
                'men',
                'prayer',
                'bible_study',
                'training',
                'conference',
                'other'
            ])->default('service')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('type', ['service', 'meeting', 'outreach', 'social', 'other'])
                ->default('service')->change();
        });
    }
};
