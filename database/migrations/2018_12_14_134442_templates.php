<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Templates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE templates_seq;

CREATE TABLE templates (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'templates_seq\'),
  template_group_id int check (template_group_id > 0) NOT NULL,
  file_link varchar(255) DEFAULT \'\',
  state int NOT NULL DEFAULT \'1\',
  term bigint NOT NULL DEFAULT \'604800\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  name varchar(255) NOT NULL DEFAULT \'\',
  html_pdf text NOT NULL,
  subject varchar(45) DEFAULT NULL,
  PRIMARY KEY (id)
)   ;

ALTER SEQUENCE templates_seq RESTART WITH 2;

CREATE INDEX templates_template_group_id_foreign ON templates (template_group_id);

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
