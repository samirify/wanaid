<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop legacy blogs and causes tables/views from the old schema.
     * New system uses client_modules + client_module_categories with
     * module tables cl_blog and cl_charity_causes.
     */
    public function up(): void
    {
        // Drop views first (depend on tables)
        DB::statement('DROP VIEW IF EXISTS v_blogs');
        DB::statement('DROP VIEW IF EXISTS cl_v_causes');

        // Drop tables
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('cl_causes');
    }

    /**
     * Reverse the migrations (restore not supported for legacy schema).
     */
    public function down(): void
    {
        // Legacy tables/views are not recreated on rollback.
        // Restore from backup if needed.
    }
};
