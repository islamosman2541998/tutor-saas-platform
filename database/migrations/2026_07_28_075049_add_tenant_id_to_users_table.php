<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')
                ->constrained('tenants')->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('image')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('image');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->unsignedBigInteger('created_by')->nullable()->after('last_login_at');
            $table->softDeletes();

            $table->unique(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['phone', 'image', 'is_active', 'last_login_at', 'created_by', 'deleted_at']);
        });
    }
};
