<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHousingTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('housing_templates',function (Blueprint $table){
            $table->string('state')->default('No Access')->description('state of send No Access,First Access,Second Access')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('housing_templates',function (Blueprint $table){
            $table->integer('state')->default(1)->description('state of send 1,2,3')->change();
        });
    }
}
