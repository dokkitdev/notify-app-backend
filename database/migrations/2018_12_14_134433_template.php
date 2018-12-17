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
CREATE TABLE `template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `tag` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `alias` varchar(45) DEFAULT NULL,
  `template_parent_id` int(11) DEFAULT NULL,
  `html_body` longtext,
  `html` longtext,
  `subject` varchar(45) DEFAULT NULL,
  `docx` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `is_html` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_template_1_idx` (`template_parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

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
