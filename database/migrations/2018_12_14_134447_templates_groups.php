<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TemplatesGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE templates_groups_seq;

CREATE TABLE templates_groups (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'templates_groups_seq\'),
  customer_group_tag varchar(255) NOT NULL DEFAULT \'\',
  customer_group_tag_id int NOT NULL DEFAULT \'0\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE templates_groups_seq RESTART WITH 2;


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
