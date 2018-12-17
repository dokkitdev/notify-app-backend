<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NHousingJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE TABLE `n_housing_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(255) DEFAULT NULL,
  `given_name` varchar(255) DEFAULT NULL,
  `family_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `stage` varchar(255) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `n_housing_jobcol` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_proccessed` int(1) DEFAULT NULL,
  `docx` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `cell_phone` varchar(255) DEFAULT NULL,
  `work_phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `schedule_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

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
