<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentsProcessed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE appointments_processed_seq;

CREATE TABLE appointments_processed (
  id int NOT NULL DEFAULT NEXTVAL (\'appointments_processed_seq\'),
  updated_at timestamp(0) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  job_id int DEFAULT NULL,
  date timestamp(0) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE appointments_processed_seq RESTART WITH 4;
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

