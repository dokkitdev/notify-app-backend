<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Template extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('tag');
            $table->string('alias');
            $table->longText('html_body');
            $table->longText('html');
            $table->string('subject');
            $table->string('docx');
            $table->string('pdf');
            $table->integer('is_html');

            $table->integer('term');
            $table->integer('template_parent_id');

            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
