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
       CREATE TABLE housing_templates (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  housing_template_group_id int(10) unsigned NOT NULL,
  state varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'No Access\',
  name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  html_pdf longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
