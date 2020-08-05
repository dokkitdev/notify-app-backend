<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewCostCenters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\CostCenter::query()->delete();

        Schema::table(
            \App\Models\CostCenter::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('type');
            }
        );

        Schema::table(
            \App\Models\CostCenter::TABLE,
            function (Blueprint $table) {
                $table->enum(
                    'type',
                    [
                        \App\Models\CostCenter::TYPE_ELECTRIC,
                        \App\Models\CostCenter::TYPE_GAS,
                        \App\Models\CostCenter::TYPE_OTHER,
                    ]
                );
            }
        );


        $costCenters = [
            ['168', 'Air Conditioning Work', \App\Models\CostCenter::TYPE_OTHER],
            ['179', 'Biomass Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['194', 'Cat 3 Water Risk Assessment', \App\Models\CostCenter::TYPE_OTHER],
            ['65', 'Electric Planned Maintenance', \App\Models\CostCenter::TYPE_ELECTRIC],
            ['99', 'Electric Planned Maintenance Commercial', \App\Models\CostCenter::TYPE_ELECTRIC],
            ['56', 'Gas Planned Maintenance', \App\Models\CostCenter::TYPE_GAS],
            ['91', 'Gas Planned Maintenance Commercial', \App\Models\CostCenter::TYPE_GAS],
            ['123', 'Gas Planned Maintenance Commercial Catering', \App\Models\CostCenter::TYPE_GAS],
            ['180', 'Heat Pump Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['169', 'Heat Recovery and Vent Systems', \App\Models\CostCenter::TYPE_OTHER],
            ['177', 'Heating Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['117', 'Lightning Protection work', \App\Models\CostCenter::TYPE_OTHER],
            ['185', 'LPG Planned Maintenance', \App\Models\CostCenter::TYPE_GAS],
            ['93', 'LPG Planned Maintenance Commercial', \App\Models\CostCenter::TYPE_GAS],
            ['126', 'LPG Planned Maintenance Commercial Catering', \App\Models\CostCenter::TYPE_GAS],
            ['181', 'Oil Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['171', 'Oil Planned Maintenance - Commercial', \App\Models\CostCenter::TYPE_OTHER],
            ['188', 'Oil Vaporizing planned maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['119', 'Smartline Project Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['96', 'Solar PV Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['182', 'Solar Thermal Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['183', 'Solid Fuel Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
            ['184', 'Unvented Domestic Hot Water Cylinder Planned Maintenance', \App\Models\CostCenter::TYPE_OTHER],
        ];
        foreach ($costCenters as $costCenter) {
            \App\Models\CostCenter::create(
                [
                    'cost_center_id' => $costCenter[0],
                    'name' => $costCenter[1],
                    'type' => $costCenter[2],
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
        //
    }
}
