<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('multiple_choice');
            $table->longText('question_text');
            $table->decimal('score', 5, 2)->default(1);
            $table->text('explanation')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
