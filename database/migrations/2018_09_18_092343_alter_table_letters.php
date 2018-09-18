<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLetters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letters',function (Blueprint $table){
            $table->integer('job_id')->default(0)->after('contract_id');
            $table->integer('housing_template_id')->default(0)->after('template_id');

            $table->dropColumn('generated');
            $table->dropColumn('sended');

            $table->dateTime('generated_at')->nullable()->after('tosend');
            $table->dateTime('sended_at')->nullable()->after('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letters',function (Blueprint $table){
            $table->dropColumn('job_id');
            $table->dropColumn('housing_template_id');

            $table->integer('generated')->default(0)->nullable()->after('tosend');
            $table->integer('sended')->default(0)->nullable()->after('generated');

            $table->dropColumn('generated_at');
            $table->dropColumn('sended_at');
        });
    }
}
