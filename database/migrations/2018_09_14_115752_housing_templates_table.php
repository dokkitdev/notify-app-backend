<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HousingTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('housing_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('housing_template_group_id')->unsigned();
            $table->foreign('housing_template_group_id')->references('id')->on('housing_templates_groups')->onDelete('cascade');
            $table->integer('state')->default(1)->description('state of send 1,2,3');
            $table->string('name')->default('');
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
        Schema::dropIfExists('housing_templates');
    }
}
