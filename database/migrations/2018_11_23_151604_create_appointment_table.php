<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `given_name` varchar(45) DEFAULT NULL,
  `family_name` varchar(45) DEFAULT NULL,
  `state` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(45) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `send_date` datetime DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `postcode` varchar(45) DEFAULT NULL,
  `work_type` varchar(45) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `time` varchar(45) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `docx` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=latin1;

');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
