<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE n_assets_seq;

CREATE TABLE n_assets (
  id int NOT NULL DEFAULT NEXTVAL (\'n_assets_seq\'),
  asset_id int DEFAULT NULL,
  site_id int DEFAULT NULL,
  contract_id int DEFAULT NULL,
  value varchar(255) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE n_assets_seq RESTART WITH 3517;
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
