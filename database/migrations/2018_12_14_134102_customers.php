<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
      CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `given_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `family_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `simpro_id` int(11) NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `customer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `customer_group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `apiurl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  `customer_group_tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT \'\',
  `customer_group_tag_id` int(11) DEFAULT \'0\',
  `parsedData` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `letter_state` int(11) NOT NULL DEFAULT \'0\',
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11646 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
