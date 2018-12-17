<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentsLogged extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE appointments_logged_seq;

CREATE TABLE appointments_logged (
  id int NOT NULL DEFAULT NEXTVAL (\'appointments_logged_seq\'),
  job_id int DEFAULT NULL,
  send_date timestamp(0) DEFAULT NULL,
  site_id int DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE appointments_logged_seq RESTART WITH 11100;
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
