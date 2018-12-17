<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HousingTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE SEQUENCE housing_templates_seq;

CREATE TABLE housing_templates (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'housing_templates_seq\'),
  housing_template_group_id int check (housing_template_group_id > 0) NOT NULL,
  state varchar(255) NOT NULL DEFAULT \'No Access\',
  name varchar(255) NOT NULL DEFAULT \'\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  html_pdf longtext NOT NULL,
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE housing_templates_seq RESTART WITH 7;

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
