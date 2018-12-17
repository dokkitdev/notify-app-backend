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
            $table->string('job_name');
            $table->string('given_name');
            $table->string('family_name');

            $table->integer('company_id');
            $table->string('company_name');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('due_date');
            $table->dateTime('schedule_date');

            $table->string('tags');
            $table->string('stage');
            $table->integer('site_id');
            $table->integer('job_id');
            $table->string('address');
            $table->string('n_housing_jobcol');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->integer('is_proccessed');
            $table->string('docx');
            $table->string('pdf');
            $table->string('cell_phone');
            $table->string('work_phone');
            $table->string('email');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
