<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('leader_id')->nullable()->constrained('members')->onDelete('set null');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('meeting_schedule')->nullable();
            $table->string('category')->default('ministry'); // ministry, service, outreach, fellowship, etc.
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('department_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // leader, co-leader, member, volunteer
            $table->date('joined_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['department_id', 'member_id']);
        });

        Schema::create('department_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // meeting, outreach, training, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('activity_date');
            $table->json('attendance')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_activities');
        Schema::dropIfExists('department_member');
        Schema::dropIfExists('departments');
    }
};
