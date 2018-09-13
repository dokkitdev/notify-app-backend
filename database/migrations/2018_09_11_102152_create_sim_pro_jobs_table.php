<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimProJobsTable extends Migration
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
            $table->string('status')->nullable();
            $table->bigInteger('set_status_date')->nullable();
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
        Schema::dropIfExists('sim_pro_jobs');
    }
}
