<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
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
            $table->string('email')->default('');
            $table->string('company_name')->default('');
            $table->string('given_name')->default('');
            $table->string('family_name')->default('');
            $table->integer('simpro_id')->unique;
            $table->string('address')->default('');
            $table->string('city')->default('');
            $table->string('state')->default('');
            $table->string('postal_code')->default('');
            $table->string('country')->default('');
            $table->string('customer_type')->default('');
            $table->string('customer_group')->default('');
            $table->string('apiurl')->default('');

            $table->string('customer_group_tag')->default('')->nullable();
            $table->integer('customer_group_tag_id')->default(0)->nullable();
            $table->text('parsedData');



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
        Schema::dropIfExists('customers');
    }
}
