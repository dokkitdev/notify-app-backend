<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimProJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sim_pro_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('simpro_id');
            $table->integer('simpro_customer_id');
            $table->text('parsedData');
            $table->string('status');
            $table->bigInteger('set_status_date');
            $table->integer('confirm');
            $table->integer('delete');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
