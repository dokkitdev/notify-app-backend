<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Template extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE template_seq;

CREATE TABLE template (
  id int NOT NULL DEFAULT NEXTVAL (\'template_seq\'),
  title varchar(45) DEFAULT NULL,
  term int DEFAULT NULL,
  tag varchar(45) DEFAULT NULL,
  created_at timestamp(0) DEFAULT NULL,
  updated_at timestamp(0) DEFAULT NULL,
  alias varchar(45) DEFAULT NULL,
  template_parent_id int DEFAULT NULL,
  html_body longtext,
  html longtext,
  subject varchar(45) DEFAULT NULL,
  docx varchar(255) DEFAULT NULL,
  pdf varchar(255) DEFAULT NULL,
  is_html smallint DEFAULT NULL,
  PRIMARY KEY (id)
)  ;

ALTER SEQUENCE template_seq RESTART WITH 15;

CREATE INDEX fk_template_1_idx ON template (template_parent_id);

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
