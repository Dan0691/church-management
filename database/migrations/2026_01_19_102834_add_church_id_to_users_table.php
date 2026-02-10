<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('church_id')->nullable()->constrained('churches')->onDelete('cascade');
            $table->enum('role', ['admin', 'pastor', 'member'])->default('member');
            $table->string('phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropColumn(['church_id', 'role', 'phone']);
        });
    }
};
