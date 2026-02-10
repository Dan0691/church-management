<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->json('data')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read']);
            $table->index(['scheduled_at', 'read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
