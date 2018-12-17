<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HousingTemplatesGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE housing_templates_groups_seq;

CREATE TABLE housing_templates_groups (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'housing_templates_groups_seq\'),
  customer_id int NOT NULL DEFAULT \'0\',
  company_name varchar(255) NOT NULL DEFAULT \'\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE housing_templates_groups_seq RESTART WITH 5;

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
