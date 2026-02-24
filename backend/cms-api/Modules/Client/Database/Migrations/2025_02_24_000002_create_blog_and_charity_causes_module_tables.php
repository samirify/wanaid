<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create cl_blog and cl_charity_causes tables and views (module pattern, no custom columns).
     * Ensures tables exist for make reset even if seeders run before or fail.
     */
    public function up(): void
    {
        $this->createModuleTable('cl_blog', 'cl_v_blog');
        $this->createModuleTable('cl_causes', 'cl_v_causes');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS cl_v_blog');
        DB::statement('DROP VIEW IF EXISTS cl_v_causes');
        Schema::dropIfExists('cl_blog');
        Schema::dropIfExists('cl_causes');
    }

    private function createModuleTable(string $tableName, string $viewName): void
    {
        DB::statement("DROP VIEW IF EXISTS {$viewName}");
        Schema::dropIfExists($tableName);

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('slogan')->nullable();
            $table->mediumText('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->boolean('active')->default(0);
            $table->longText('options')->nullable();
            $table->timestamps();
            createUserStampFields($table);
        });

        DB::statement("CREATE OR REPLACE VIEW {$viewName} AS SELECT * FROM {$tableName} AS c");
    }
};
