<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetReportsMini extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'asset_reports_mini',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('site_id')->nullable();
                $table->string('uprn')->nullable();
                $table->integer('asset_id')->nullable();
                $table->string('asset_type')->nullable();
                $table->string('type')->nullable();
                $table->string('fuel_type')->nullable();
                $table->string('make')->nullable();
                $table->string('model')->nullable();
                $table->date('last_service_date')->nullable();
                $table->date('service_level_start_date')->nullable();
                $table->date('job_due_date')->nullable();
                $table->date('next_service_date')->nullable();
                $table->string('job_stage')->nullable();
                $table->string('service_level_name')->nullable();
                $table->string('last_MOT_date')->nullable();
                $table->date('service_due')->nullable();
                $table->string('next_scheduled_appointment_date')->nullable();
                $table->string('no_access_visits')->nullable();
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
        //
    }
}
