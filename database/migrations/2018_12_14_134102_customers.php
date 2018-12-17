<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('company_name');
            $table->string('given_name');
            $table->string('family_name');
            $table->integer('simpro_id');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->string('customer_type');
            $table->string('customer_group');
            $table->string('apiurl');
            $table->string('customer_group_tag');
            $table->string('customer_group_tag_id');
            $table->string('parsedData');
            $table->integer('letter_state');
            $table->integer('company_id');
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
            $table->dateTime('date');
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
