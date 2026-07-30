<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SQLite (used by the test suite, see phpunit.xml) has no ENUM type —
     * Schema::enum() there is just a CHECK constraint that SQLite can't
     * ALTER in place, and it doesn't need one since only this app ever
     * writes to the column. The raw MODIFY below is MySQL-only syntax and
     * only runs against the real driver.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY status ENUM('pending', 'active', 'suspended', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
        }
    }
};
