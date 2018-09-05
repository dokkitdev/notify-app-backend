<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_group_id')->unsigned();
            $table->foreign('template_group_id')->references('id')->on('templates_groups')->onDelete('cascade');
            $table->string('file_link');
            $table->enum('state',[1,2,3])->default(1)->description('state of send');
            $table->bigInteger('term')->default(604800)->description('term of send');
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
        Schema::dropIfExists('templates');
    }
}
