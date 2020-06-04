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
            53 => 'Electric Responsive Repair',
            89 => 'Electric Responsive Repair Commercial',
            78 => 'Electrical Installation',
            178 => 'Emergency Response /Assistance - Alternative Skill Set',
            134 => 'Energy Performance Certification (EPC)',
        ];

        $gas = [
            123 => 'Gas Planned Maintenance Commercial Catering',
            43 => 'Gas Responsive Repair',
            80 => 'Gas Responsive Repair Commercial',
            135 => 'Lead/Quotation',
            186 => 'Warranty Repair - Potterton/Baxi',
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
