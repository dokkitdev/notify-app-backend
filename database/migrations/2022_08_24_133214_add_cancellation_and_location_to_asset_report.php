<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationAndLocationToAssetReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'asset_reports_mini',
            function (Blueprint $table) {
                $table->string('location')->nullable();
                $table->string('cancellation')->nullable();
            }
        );
        Schema::table(
            'asset_reports',
            function (Blueprint $table) {
                $table->string('location')->nullable();
                $table->string('cancellation')->nullable();
                $table->boolean('is_updated')->default(1);
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
        //
    }
}
