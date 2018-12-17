<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Letters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE TABLE `letters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int(10) unsigned DEFAULT NULL,
  `job_id` int(11) NOT NULL DEFAULT \'0\',
  `template_id` int(10) unsigned DEFAULT NULL,
  `housing_template_id` int(11) NOT NULL DEFAULT \'0\',
  `tosend` int(11) DEFAULT \'1\',
  `generated_at` datetime DEFAULT NULL,
  `sended_at` datetime DEFAULT NULL,
  `letter` int(11) DEFAULT \'0\',
  `email` int(11) DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `simpro_attachment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `letter_s3_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  PRIMARY KEY (`id`),
  KEY `private_letters_contract_id_foreign` (`contract_id`),
  KEY `private_letters_template_id_foreign` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
