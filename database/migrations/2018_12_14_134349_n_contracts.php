<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE n_contracts_seq;

CREATE TABLE n_contracts (
  id int NOT NULL DEFAULT NEXTVAL (\'n_contracts_seq\'),
  contract_id int DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  end_date timestamp(0) DEFAULT NULL,
  value varchar(255) DEFAULT NULL,
  customer_id int DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  is_processed_1 int DEFAULT NULL,
  is_processed_4 int DEFAULT NULL,
  is_processed_8 int DEFAULT NULL,
  docx varchar(255) DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  contract_no varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE n_contracts_seq RESTART WITH 111;

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
