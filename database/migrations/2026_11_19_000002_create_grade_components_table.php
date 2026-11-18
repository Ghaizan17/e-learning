<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_category_id')->constrained()->cascadeOnDelete();
            $table->morphs('gradeable');
            $table->string('title');
            $table->decimal('max_score', 6, 2)->default(100);
            $table->timestamps();

            $table->unique(['gradeable_type', 'gradeable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_components');
    }
};
