<?php

use App\Models\CostCenter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostCenter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CostCenter::updateOrCreate(
            [
                'cost_center_id' => 206,
            ],
            [
                'type' => CostCenter::TYPE_ELECTRIC,
                'name' => 'Electric - EICR Remedial Work'
            ]
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
