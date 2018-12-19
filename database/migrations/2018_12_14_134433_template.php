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
            $table->string('title')->nullable();
            $table->string('tag')->nullable();
            $table->string('alias')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('html')->nullable();
            $table->string('subject')->nullable();
            $table->string('docx')->nullable();
            $table->string('pdf')->nullable();
            $table->integer('is_html')->nullable();

            $table->integer('term')->nullable();
            $table->integer('template_parent_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('template');
    }
}
