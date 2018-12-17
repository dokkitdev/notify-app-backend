<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
      CREATE SEQUENCE customers_seq;

CREATE TABLE customers (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'customers_seq\'),
  email varchar(255) NOT NULL DEFAULT \'\',
  company_name varchar(255) NOT NULL DEFAULT \'\',
  given_name varchar(255) NOT NULL DEFAULT \'\',
  family_name varchar(255) NOT NULL DEFAULT \'\',
  simpro_id int NOT NULL,
  address varchar(255) NOT NULL DEFAULT \'\',
  city varchar(255) NOT NULL DEFAULT \'\',
  state varchar(255) NOT NULL DEFAULT \'\',
  postal_code varchar(255) NOT NULL DEFAULT \'\',
  country varchar(255) NOT NULL DEFAULT \'\',
  customer_type varchar(255) NOT NULL DEFAULT \'\',
  customer_group varchar(255) NOT NULL DEFAULT \'\',
  apiurl varchar(255) NOT NULL DEFAULT \'\',
  customer_group_tag varchar(255) DEFAULT \'\',
  customer_group_tag_id int DEFAULT \'0\',
  parsedData text NOT NULL,
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  letter_state int NOT NULL DEFAULT \'0\',
  company_id int DEFAULT NULL,
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE customers_seq RESTART WITH 11646;

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
