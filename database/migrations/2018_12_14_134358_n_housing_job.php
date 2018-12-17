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
CREATE SEQUENCE n_housing_job_seq;

CREATE TABLE n_housing_job (
  id int NOT NULL DEFAULT NEXTVAL (\'n_housing_job_seq\'),
  job_name varchar(255) DEFAULT NULL,
  given_name varchar(255) DEFAULT NULL,
  family_name varchar(255) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  company_name varchar(255) DEFAULT NULL,
  due_date timestamp(0) DEFAULT NULL,
  tags varchar(255) DEFAULT NULL,
  stage varchar(255) DEFAULT NULL,
  site_id int DEFAULT NULL,
  job_id int DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  n_housing_jobcol varchar(255) DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  state varchar(255) DEFAULT NULL,
  postal_code varchar(255) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  is_proccessed int DEFAULT NULL,
  docx varchar(255) DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  cell_phone varchar(255) DEFAULT NULL,
  work_phone varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  schedule_date timestamp(0) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE n_housing_job_seq RESTART WITH 59;

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
