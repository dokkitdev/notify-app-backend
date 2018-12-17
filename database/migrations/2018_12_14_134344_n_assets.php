<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('n_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('asset_id');
            $table->integer('site_id');
            $table->integer('contract_id');
            $table->string('value');
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
