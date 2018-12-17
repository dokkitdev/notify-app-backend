<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Appointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE appointments_seq;

CREATE TABLE appointments (
  id int NOT NULL DEFAULT NEXTVAL (\'appointments_seq\'),
  title varchar(45) DEFAULT NULL,
  given_name varchar(45) DEFAULT NULL,
  family_name varchar(45) DEFAULT NULL,
  state varchar(45) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  country varchar(45) DEFAULT NULL,
  job_id int DEFAULT NULL,
  send_date timestamp(0) DEFAULT NULL,
  appointment_id int DEFAULT NULL,
  site_id int DEFAULT NULL,
  postcode varchar(45) DEFAULT NULL,
  work_type varchar(45) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  time varchar(45) DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  docx varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE appointments_seq RESTART WITH 181;

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
