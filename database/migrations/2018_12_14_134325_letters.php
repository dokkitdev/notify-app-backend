<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Letters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id')->nullable();
            $table->integer('job_id')->nullable();
            $table->integer('template_id')->nullable();
            $table->integer('housing_template_id')->nullable();
            $table->integer('tosend')->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('sended_at')->nullable();
            $table->integer('letter')->nullable();
            $table->integer('email')->nullable();
            $table->integer('simpro_attachment_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('letter_s3_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('letters');
    }
}
