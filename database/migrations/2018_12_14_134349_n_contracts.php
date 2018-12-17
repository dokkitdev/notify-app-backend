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
CREATE TABLE n_contracts (
  id int(11) NOT NULL AUTO_INCREMENT,
  contract_id int(11) DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  end_date datetime DEFAULT NULL,
  value varchar(255) DEFAULT NULL,
  customer_id int(11) DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  is_processed_1 int(1) DEFAULT NULL,
  is_processed_4 int(1) DEFAULT NULL,
  is_processed_8 int(1) DEFAULT NULL,
  docx varchar(255) DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  contract_no varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=latin1;

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
