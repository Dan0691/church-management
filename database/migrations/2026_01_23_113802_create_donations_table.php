<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('donation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('donation_type_id')->constrained()->onDelete('restrict');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('payment_method')->default('cash'); // cash, check, card, transfer, online
            $table->string('check_number')->nullable();
            $table->string('receipt_number')->unique()->nullable();
            $table->date('donation_date');
            $table->string('frequency')->default('one-time'); // one-time, weekly, monthly, yearly
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->date('next_payment_date')->nullable();
            $table->timestamps();
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('pledge_type'); // building, mission, general, specific
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('payment_frequency'); // monthly, weekly, one-time
            $table->json('payment_schedule')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pledges');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_types');
    }
};
