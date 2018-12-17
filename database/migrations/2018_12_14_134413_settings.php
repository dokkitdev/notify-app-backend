<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Settings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE settings_seq;

CREATE TABLE settings (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'settings_seq\'),
  name varchar(255) NOT NULL DEFAULT \'\',
  value varchar(255) NOT NULL DEFAULT \'\',
  expires_in varchar(255) NOT NULL DEFAULT \'\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  wait int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE settings_seq RESTART WITH 6;

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
