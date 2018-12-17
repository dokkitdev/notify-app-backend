<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimProContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sim_pro_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customers_id');
            $table->integer('simpro_id');
            $table->text('parsedData');
            $table->bigInteger('start_date');
            $table->bigInteger('end_date');
            $table->string('contract_no');
            $table->string('contract_name');
            $table->integer('active');
            $table->integer('confirm');

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
