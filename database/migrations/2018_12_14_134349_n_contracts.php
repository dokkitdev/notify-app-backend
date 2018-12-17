<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('n_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id');
            $table->string('name');
            $table->dateTime('end_date');
            $table->string('value');
            $table->integer('customer_id');

            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('is_processed_1');
            $table->integer('is_processed_4');
            $table->integer('is_processed_8');
            $table->string('docx');
            $table->string('pdf');
            $table->string('contract_no');
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
