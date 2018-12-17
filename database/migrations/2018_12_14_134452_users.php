<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE users_seq;

CREATE TABLE users (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'users_seq\'),
  name varchar(255) NOT NULL,
  email varchar(255) DEFAULT NULL,
  password varchar(255) NOT NULL DEFAULT \'\',
  role enum(\'admin\',\'user\') NOT NULL DEFAULT \'user\',
  active int NOT NULL DEFAULT \'1\',
  remember_token varchar(100) DEFAULT NULL,
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT email UNIQUE (email)
)   ;

ALTER SEQUENCE users_seq RESTART WITH 7;

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
