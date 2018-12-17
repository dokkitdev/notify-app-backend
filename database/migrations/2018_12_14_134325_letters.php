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
            $table->integer('contract_id');
            $table->integer('job_id');
            $table->integer('template_id');
            $table->integer('housing_template_id');
            $table->integer('tosend');
            $table->dateTime('generated_at');
            $table->dateTime('sended_at');
            $table->integer('letter');
            $table->integer('email');
            $table->integer('simpro_attachment_id');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('letter_s3_link');
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
