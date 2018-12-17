<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Appointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('given_name');
            $table->string('family_name');
            $table->string('state');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->integer('job_id');
            $table->dateTime('send_date');
            $table->integer('appointment_id');
            $table->integer('site_id');
            $table->string('postcode');
            $table->string('work_type');
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
            $table->string('time');
            $table->string('pdf');
            $table->string('docx');
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
