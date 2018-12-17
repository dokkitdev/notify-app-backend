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
CREATE SEQUENCE logs_seq;

CREATE TABLE logs (
  id int NOT NULL DEFAULT NEXTVAL (\'logs_seq\'),
  customer_type varchar(45) DEFAULT NULL,
  letters_generated int DEFAULT NULL,
  email_generated int DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  is_started int NOT NULL DEFAULT \'0\',
  is_finished int NOT NULL DEFAULT \'0\',
  command longtext,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE logs_seq RESTART WITH 56;


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
