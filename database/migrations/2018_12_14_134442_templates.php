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
CREATE TABLE templates (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  template_group_id int(10) unsigned NOT NULL,
  file_link varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT \'\',
  state int(11) NOT NULL DEFAULT \'1\',
  term bigint(20) NOT NULL DEFAULT \'604800\',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  html_pdf text COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (id),
  KEY templates_template_group_id_foreign (template_group_id)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
