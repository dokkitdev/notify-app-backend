<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToChl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $parent = \App\TemplateParent::where('title', 'CHL Letters')->first();
        if ($parent) {
            $parent->title = 'CHL Other Letters';
            $parent->save();
        }

        $parent = \App\TemplateParent::create([
            'title' => 'CHL Gas Letters',
            'order' => 3,
        ]);
        $templates = $parent->templates();


        $templatesArray = [
            'Appointment Letter 1' => \App\Templates::APPOINTMENT_LETTER_GAS_CHL_1,
            'Appointment Letter 2' => \App\Templates::APPOINTMENT_LETTER_GAS_CHL_2,
            'Appointment Letter 3' => \App\Templates::APPOINTMENT_LETTER_GAS_CHL_3,
        ];
        foreach ($templatesArray as $title => $alias) {
            $templates->save(
                \App\Templates::create([
                    'title' => $title,
                    'alias' => $alias,
                    'is_html' => 0,
                ])
            );
        }

        $parent = \App\TemplateParent::create([
            'title' => 'CHL Electric Letters',
            'order' => 3,
        ]);
        $templates = $parent->templates();
        $templatesArray = [
            'Appointment Letter 1' => \App\Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_1,
            'Appointment Letter 2' => \App\Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_2,
            'Appointment Letter 3' => \App\Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_3,
        ];
        foreach ($templatesArray as $title => $alias) {
            $templates->save(
                \App\Templates::create([
                    'title' => $title,
                    'alias' => $alias,
                    'is_html' => 0,
                ])
            );
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
