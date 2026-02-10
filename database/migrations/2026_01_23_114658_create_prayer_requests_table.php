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
        Schema::create('prayer_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('church_id')->constrained()->onDelete('cascade');
        $table->foreignId('member_id')->nullable()->constrained()->onDelete('set null');
        $table->string('title');
        $table->text('request');
        $table->string('category')->default('general'); // healing, financial, guidance, thanksgiving, etc.
        $table->string('privacy')->default('public'); // public, church_only, private
        $table->string('status')->default('pending'); // pending, praying, answered, closed
        $table->integer('prayer_count')->default(0);
        $table->date('target_date')->nullable();
        $table->json('answers')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_requests');
    }
};
