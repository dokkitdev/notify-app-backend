<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'asset_report_filters',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('site_id');
                $table->text('service_levels')->nullable();
                $table->text('asset_types')->nullable();
                $table->text('errors')->nullable();
                $table->text('stages')->nullable();
                $table->timestamps();

            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_report_filters');
    }
}
