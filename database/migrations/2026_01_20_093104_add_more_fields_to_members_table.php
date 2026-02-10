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
        Schema::table('members', function (Blueprint $table) {
            // Add missing columns
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('zip_code');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('occupation', 255)->nullable()->after('marital_status');
            $table->text('notes')->nullable()->after('occupation');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');

            // Add foreign key constraint for created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Also make sure existing columns are correct
            $table->string('phone', 20)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['created_by']);

            // Drop columns
            $table->dropColumn(['gender', 'marital_status', 'occupation', 'notes', 'created_by']);

            // Revert column changes if needed
            $table->string('phone', 20)->change();
            $table->string('email', 255)->change();
        });
    }
};
