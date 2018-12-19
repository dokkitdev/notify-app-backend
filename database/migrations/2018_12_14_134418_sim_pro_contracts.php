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
            $table->integer('customers_id')->nullable();
            $table->integer('simpro_id')->nullable();
            $table->text('parsedData')->nullable();
            $table->bigInteger('start_date')->nullable();
            $table->bigInteger('end_date')->nullable();
            $table->string('contract_no')->nullable();
            $table->string('contract_name')->nullable();
            $table->integer('active')->nullable();
            $table->integer('confirm')->nullable();

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
        Schema::drop('sim_pro_contracts');
    }
}
