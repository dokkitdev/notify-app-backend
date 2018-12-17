<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Logs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_type');
            $table->integer('letters_generated');
            $table->integer('email_generated');
            $table->string('pdf');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('is_started');
            $table->integer('is_finished');
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
