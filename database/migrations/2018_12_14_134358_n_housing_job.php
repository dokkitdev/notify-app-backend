<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NHousingJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('n_housing_job', function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();

            $table->integer('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('schedule_date')->nullable();

            $table->string('tags')->nullable();
            $table->string('stage')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('job_id')->nullable();
            $table->string('address')->nullable();
            $table->string('n_housing_jobcol')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('is_proccessed')->nullable();
            $table->string('docx')->nullable();
            $table->string('pdf')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('email')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('n_housing_job');
    }
}
