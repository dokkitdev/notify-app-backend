<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PdfHtmlLongtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('housing_templates', function (Blueprint $table){
            $table->dropColumn('html_pdf');
        });

        Schema::table('housing_templates', function (Blueprint $table){
            $table->longtext('html_pdf');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('housing_templates', function (Blueprint $table){
            $table->dropColumn('html_pdf');
        });

        Schema::table('housing_templates', function (Blueprint $table){
            $table->text('html_pdf');
        });
    }
}
