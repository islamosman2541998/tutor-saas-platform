<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('group_id')->constrained()->restrictOnDelete();
            $table->dateTime('requested_at');
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['waiting', 'contacted', 'accepted', 'cancelled'])->default('waiting');
            $table->timestamps();

            $table->index(['tenant_id', 'group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_entries');
    }
};
