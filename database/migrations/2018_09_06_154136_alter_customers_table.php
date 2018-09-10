<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers',function (Blueprint $table){
            $table->string('customer_group_tag')->default('')->nullable();
            $table->integer('customer_group_tag_id')->default(0)->nullable();
            $table->string('start_date')->default('')->nullable();
            $table->string('end_date')->default('')->nullable();
            $table->string('contract_no')->default('')->nullable();
            $table->string('contract_name')->default('')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers',function (Blueprint $table){
            $table->dropColumn('customer_group_tag');
            $table->dropColumn('customer_group_tag_id');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('contract_no');
            $table->dropColumn('contract_name');
        });
    }
}
