<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type', 20)->default('text');
            $table->longText('content_html')->nullable();
            $table->string('video_url')->nullable();
            $table->string('external_url')->nullable();
            $table->string('code_language', 40)->nullable();
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['chapter_id', 'position']);
            $table->index(['chapter_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
