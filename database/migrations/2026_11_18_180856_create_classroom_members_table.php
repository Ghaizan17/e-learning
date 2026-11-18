<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['classroom_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_members');
    }
};
