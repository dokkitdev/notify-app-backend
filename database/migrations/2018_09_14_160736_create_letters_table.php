<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('private_letters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id')->unsigned()->nullable();
            $table->foreign('contract_id')->references('id')->on('sim_pro_contracts')->onDelete('set null');
            $table->integer('template_id')->unsigned()->nullable();
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('set null');
            $table->integer('tosend')->default(1)->nullable();
            $table->integer('generated')->default(0)->nullable();
            $table->integer('sended')->default(0)->nullable();
            $table->integer('letter')->default(0)->nullable();
            $table->integer('email')->default(0)->nullable();
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
        Schema::dropIfExists('private_letters');
    }
}
