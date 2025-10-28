<?php

use App\Models\TemplateParent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $append = [
            [
                'title' => 'CHL Electric Remedial Work Letters',
                'templates' => [
                    [
                        'term' => null,
                        'title' => 'Appointment Remedial Work Letter 1',
                        'tag' => null,
                        'alias' => 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_1',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 0,
                    ],
                    [
                        'term' => null,
                        'title' => 'Appointment Remedial Work Letter 2',
                        'tag' => null,
                        'alias' => 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_2',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 0,
                    ],
                    [
                        'term' => null,
                        'title' => 'Appointment Remedial Work Letter 3',
                        'tag' => null,
                        'alias' => 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_3',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 0,
                    ],
                ]
            ]
        ];

        foreach ($append as $parent_template) {
            $pt = \App\Models\TemplateParent::create(['title' => $parent_template['title']]);
            foreach ($parent_template['templates'] as $template) {
                $t = \App\Models\Templates::create($template);
                $pt->templates()->save($t);
            }
        }
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
