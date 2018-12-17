<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TemplateParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE template_parent_seq;

CREATE TABLE template_parent (
  id int NOT NULL DEFAULT NEXTVAL (\'template_parent_seq\'),
  title varchar(45) DEFAULT NULL,
  created_at varchar(45) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  alias varchar(45) DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE template_parent_seq RESTART WITH 7;

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
