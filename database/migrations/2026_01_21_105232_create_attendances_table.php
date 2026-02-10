<?php
// database/migrations/2026_01_21_000001_create_attendances_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->integer('children')->default(0);
            $table->integer('men')->default(0);
            $table->integer('women')->default(0);
            $table->integer('visitors')->default(0);
            $table->integer('total')->default(0);
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
