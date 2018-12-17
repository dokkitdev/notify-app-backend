<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimProContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
    CREATE SEQUENCE sim_pro_contracts_seq;

    CREATE TABLE sim_pro_contracts (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'sim_pro_contracts_seq\'),
  customers_id int check (customers_id > 0) NOT NULL,
  simpro_id int NOT NULL,
  parsedData text NOT NULL,
  start_date bigint DEFAULT \'0\',
  end_date bigint DEFAULT \'0\',
  contract_no varchar(255) DEFAULT \'\',
  contract_name varchar(255) DEFAULT \'\',
  active int DEFAULT NULL,
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  confirm int NOT NULL DEFAULT \'0\',
  delete int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE sim_pro_contracts_seq RESTART WITH 182;

CREATE INDEX sim_pro_contracts_customers_id_foreign ON sim_pro_contracts (customers_id);

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
