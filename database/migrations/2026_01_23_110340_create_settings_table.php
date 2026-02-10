<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->foreignId('church_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['key', 'church_id']);
            $table->index(['church_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
