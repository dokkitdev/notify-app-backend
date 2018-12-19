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
            $table->integer('simpro_id')->nullable();
            $table->integer('simpro_customer_id')->nullable();
            $table->text('parsedData')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('set_status_date')->nullable();
            $table->integer('confirm')->nullable();
            $table->integer('delete')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sim_pro_jobs');
    }
}
