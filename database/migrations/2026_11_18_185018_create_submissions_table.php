<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_no')->default(1);
            $table->boolean('is_latest')->default(true);
            $table->longText('content')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id', 'attempt_no']);
            $table->index(['assignment_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['assignment_id', 'user_id', 'is_latest']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
