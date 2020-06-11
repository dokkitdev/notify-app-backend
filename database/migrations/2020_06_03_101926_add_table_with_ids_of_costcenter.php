<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableWithIdsOfCostcenter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            \App\Models\CostCenter::TABLE,
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('cost_center_id');
                $table->enum('type', [\App\Models\CostCenter::TYPE_ELECTRIC, \App\Models\CostCenter::TYPE_GAS]);
                $table->index(['cost_center_id'], 'cost_center_id_indx');
                $table->unique(['cost_center_id']);
            }
        );


        $electric = [
            99 => 'Electric Planned Maintenance Commercial',
            65 => 'Electric Planned Maintenance',
        ];

        $gas = [
            56 => 'Gas Planned Maintenance',
            91 => 'Gas Planned Maintenance Commercial',
            123 => 'Gas Planned Maintenance Commercial Catering',
            185 => 'LPG Planned Maintenance',
            93 => 'LPG Planned Maintenance Commercial',
            126 => 'LPG Planned Maintenance Commercial Catering',
        ];

        foreach ($electric as $id => $name) {
            \App\Models\CostCenter::create(
                [
                    'name' => $name,
                    'cost_center_id' => $id,
                    'type' => \App\Models\CostCenter::TYPE_ELECTRIC,
                ]
            );
        }

        foreach ($gas as $id => $name) {
            \App\Models\CostCenter::create(
                [
                    'name' => $name,
                    'cost_center_id' => $id,
                    'type' => \App\Models\CostCenter::TYPE_GAS,
                ]
            );
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(\App\Models\CostCenter::TABLE);
    }
}
