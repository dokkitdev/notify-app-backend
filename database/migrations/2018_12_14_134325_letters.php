<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Letters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE SEQUENCE letters_seq;

CREATE TABLE letters (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL (\'letters_seq\'),
  contract_id int check (contract_id > 0) DEFAULT NULL,
  job_id int NOT NULL DEFAULT \'0\',
  template_id int check (template_id > 0) DEFAULT NULL,
  housing_template_id int NOT NULL DEFAULT \'0\',
  tosend int DEFAULT \'1\',
  generated_at timestamp(0) DEFAULT NULL,
  sended_at timestamp(0) DEFAULT NULL,
  letter int DEFAULT \'0\',
  email int DEFAULT \'0\',
  created_at timestamp(0) NULL DEFAULT NULL,
  updated_at timestamp(0) NULL DEFAULT NULL,
  simpro_attachment_id varchar(255) DEFAULT NULL,
  letter_s3_link varchar(255) NOT NULL DEFAULT \'\',
  PRIMARY KEY (id)
)  ;

CREATE INDEX private_letters_contract_id_foreign ON letters (contract_id);
CREATE INDEX private_letters_template_id_foreign ON letters (template_id);

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
