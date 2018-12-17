<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Logs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_type` varchar(45) DEFAULT NULL,
  `letters_generated` int(11) DEFAULT NULL,
  `email_generated` int(11) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_started` int(1) NOT NULL DEFAULT \'0\',
  `is_finished` int(1) NOT NULL DEFAULT \'0\',
  `command` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;


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
