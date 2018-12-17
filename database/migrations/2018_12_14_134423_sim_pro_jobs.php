<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimProJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE sim_pro_jobs_seq;

CREATE TABLE sim_pro_jobs (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'sim_pro_jobs_seq\'),
  simpro_id int NOT NULL,
  simpro_customer_id int NOT NULL,
  parsedData text NOT NULL,
  status varchar(255) DEFAULT NULL,
  set_status_date bigint DEFAULT NULL,
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  confirm int NOT NULL DEFAULT \'0\',
  delete int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE sim_pro_jobs_seq RESTART WITH 65;

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
