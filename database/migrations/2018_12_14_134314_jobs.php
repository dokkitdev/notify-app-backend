<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Jobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
       CREATE TABLE jobs (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  queue varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  payload longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  attempts tinyint(3) unsigned NOT NULL,
  reserved_at int(10) unsigned DEFAULT NULL,
  available_at int(10) unsigned NOT NULL,
  created_at int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
