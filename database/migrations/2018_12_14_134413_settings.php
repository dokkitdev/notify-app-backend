<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Settings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
CREATE TABLE settings (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  value varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  expires_in varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'\',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  wait int(11) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
