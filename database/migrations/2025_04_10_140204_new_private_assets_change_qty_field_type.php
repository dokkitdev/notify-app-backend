<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewPrivateAssetsChangeQtyFieldType extends Migration
{
    public function up()
    {
        Schema::table('new_private_assets', function (Blueprint $table) {
            $table->float('qty')->change();
        });
    }

    public function down()
    {
        Schema::table('new_private_assets', function (Blueprint $table) {
            $table->integer('qty')->change();
        });
    }
}
