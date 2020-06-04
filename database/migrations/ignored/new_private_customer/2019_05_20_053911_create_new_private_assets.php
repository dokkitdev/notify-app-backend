<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewPrivatePrebuilds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_private_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('qty')->nullable();
            $table->float('ex_tax')->nullable();
            $table->float('inc_tax')->nullable();
            $table->integer('type')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('private_customer_id')->nullable();
            $table->integer('private_cost_center_id')->nullable();
            $table->index(['private_customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('new_private_assets');
    }
}
