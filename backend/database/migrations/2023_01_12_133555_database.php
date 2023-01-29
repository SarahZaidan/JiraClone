<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //status table
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->timestamps();
        });
        //projects
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->dateTime('start_time', $precision = 0)->nullable();
            $table->dateTime('due_time', $precision = 0)->nullable();
            $table->integer("status_id")->unsigned()->default(0);
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            $table->timestamps();
        });

        //user_projects table
        Schema::create('user_projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role')->default(-1);
            $table->integer("user_id")->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer("project_id")->unsigned();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->timestamps();
        });

        //section table
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->integer("project_id")->unsigned();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->integer("status_id")->unsigned()->default(0);
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            $table->timestamps();
        });


        //tasks table
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string("description");
            $table->string("priority");
            $table->dateTime('start_time', $precision = 0)->nullable();
            $table->dateTime('due_time', $precision = 0)->nullable();
            $table->integer("section_id")->unsigned();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->integer("status_id")->unsigned()->default(0);
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            $table->integer("project_id")->unsigned()->default(0);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->integer("index");
            $table->timestamps();
        });

        //assign
        Schema::create('assignments', function (Blueprint $table) {
            $table->integer("task_id")->unsigned();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->integer("user_id")->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('user_projects');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('statuses');
    }
};
