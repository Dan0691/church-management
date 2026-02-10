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
        Schema::create('sermons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('church_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('speaker');
        $table->date('sermon_date');
        $table->string('scripture_reference')->nullable();
        $table->string('series')->nullable();
        $table->integer('duration')->nullable(); // in minutes
        $table->string('audio_url')->nullable();
        $table->string('video_url')->nullable();
        $table->string('slides_url')->nullable();
        $table->string('notes_url')->nullable();
        $table->integer('views')->default(0);
        $table->integer('downloads')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};
