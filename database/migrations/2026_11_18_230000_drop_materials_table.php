<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the unused standalone materials table; lessons are the course content.
     */
    public function up(): void
    {
        if (Schema::hasTable('materials')) {
            Schema::drop('materials');
        }
    }

    public function down(): void
    {
        // The legacy scaffold table is intentionally not restorable.
    }
};
