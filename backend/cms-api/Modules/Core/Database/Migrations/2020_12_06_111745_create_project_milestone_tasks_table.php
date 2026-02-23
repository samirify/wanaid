<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectMilestoneTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_milestone_tasks', function (Blueprint $table) {
            $table->id();

            // 'project_milestones_id', 'code', 'description', 'due_date', 'status', 'active', 'contact_id'

            $table->unsignedBigInteger('project_milestones_id');
            $table->foreign('project_milestones_id')->references('id')->on('project_milestones')->cascadeOnDelete();
            $table->string('code', 32);
            $table->unique(['code', 'project_milestones_id']);
            $table->string('title', 255);
            $table->string('unique_title', 255);
            $table->mediumText('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('application_code');
            $table->boolean('active')->default(0);
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts');

            $table->timestamps();
            createUserStampFields($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_milestone_tasks');
    }
}
