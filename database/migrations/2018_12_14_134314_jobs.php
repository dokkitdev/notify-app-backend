<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Jobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE jobs_seq;

CREATE TABLE jobs (
  id bigint check (id > 0) NOT NULL DEFAULT NEXTVAL (\'jobs_seq\'),
  queue varchar(255) NOT NULL,
  payload longtext NOT NULL,
  attempts smallint check (attempts > 0) NOT NULL,
  reserved_at int check (reserved_at > 0) DEFAULT NULL,
  available_at int check (available_at > 0) NOT NULL,
  created_at int check (created_at > 0) NOT NULL,
  PRIMARY KEY (id)
)  ;

CREATE INDEX jobs_queue_index ON jobs (queue);

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
