<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimProContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
    CREATE TABLE `sim_pro_contracts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customers_id` int(10) unsigned NOT NULL,
  `simpro_id` int(11) NOT NULL,
  `parsedData` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` bigint(20) DEFAULT \'0\',
  `end_date` bigint(20) DEFAULT \'0\',
  `contract_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT \'\',
  `contract_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT \'\',
  `active` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `confirm` int(11) NOT NULL DEFAULT \'0\',
  `delete` int(11) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  KEY `sim_pro_contracts_customers_id_foreign` (`customers_id`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

');
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
