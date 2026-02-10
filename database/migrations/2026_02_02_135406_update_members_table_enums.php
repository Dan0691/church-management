<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            // Update gender enum
            DB::statement("ALTER TABLE members MODIFY COLUMN gender ENUM('Male', 'Female', 'Other') NULL");

            // Update marital_status enum (add Separated and use proper casing)
            DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed', 'Separated') NULL");

            // Update membership_status enum (add pending and transferred)
            DB::statement("ALTER TABLE members MODIFY COLUMN membership_status ENUM('Active', 'Inactive', 'Visitor', 'Pending', 'Transferred') NOT NULL DEFAULT 'Active'");
        }

        // For SQLite or PostgreSQL, you might need different syntax
        // This is for MySQL
    }

    public function down()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Revert gender enum
            DB::statement("ALTER TABLE members MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL");

            // Revert marital_status enum
            DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('single', 'married', 'divorced', 'widowed') NULL");

            // Revert membership_status enum
            DB::statement("ALTER TABLE members MODIFY COLUMN membership_status ENUM('active', 'inactive', 'visitor') NOT NULL DEFAULT 'active'");
        }
    }
};
