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
            $table->integer('contract_id')->nullable();
            $table->string('name')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('value')->nullable();
            $table->integer('customer_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('is_processed_1')->nullable();
            $table->integer('is_processed_4')->nullable();
            $table->integer('is_processed_8')->nullable();
            $table->string('docx')->nullable();
            $table->string('pdf')->nullable();
            $table->string('contract_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('n_contracts');
    }
}
