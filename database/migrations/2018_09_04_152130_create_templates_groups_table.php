<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_group_tag')->default('');
            $table->integer('customer_group_tag_id')->default(0);
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
        Schema::table('templates_groups',function (Blueprint $table){
            $table->dropColumn('customer_group_tag_id');
            $table->dropColumn('customer_group_tag');
        });
    }
}
