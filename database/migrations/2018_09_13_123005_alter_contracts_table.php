<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sim_pro_contracts',function (Blueprint $table){
            $table->integer('confirm')->default(0);
            $table->integer('delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sim_pro_contracts',function (Blueprint $table){
            $table->dropColumn('confirm');
            $table->dropColumn('delete');
        });
    }
}
