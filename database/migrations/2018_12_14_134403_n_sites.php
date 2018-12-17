<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NSites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE n_sites_seq;

CREATE TABLE n_sites (
  id int NOT NULL DEFAULT NEXTVAL (\'n_sites_seq\'),
  site_id int DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  state varchar(255) DEFAULT NULL,
  postal_code varchar(255) DEFAULT NULL,
  customer_id varchar(45) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE n_sites_seq RESTART WITH 850;

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
