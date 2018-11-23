<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $append = [
            [
                'title' => 'Private',
                'templates' => [[
                    'term' => 1,
                    'title' => 'Letter 1',
                    'tag' => null,
                    'alias' => 'PRIVATE_1_WEEK',
                    'html_body' => null,
                    'html' => null,
                    'subject' => null,
                    'docx' => null,
                    'pdf' => null,
                    'is_html' => 1,
                ],
                    [
                        'term' => 4,
                        'title' => 'Letter 2',
                        'tag' => null,
                        'alias' => 'PRIVATE_4_WEEK',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],
                    [
                        'term' => 8,
                        'title' => 'Letter 3',
                        'tag' => null,
                        'alias' => 'PRIVATE_8_WEEK',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ]
                ]
            ],
            [
                'title' => 'Housing Authorities',
                'templates' => [
                    [
                        'term' => 1,
                        'title' => 'Letter',
                        'tag' => 'No Access',
                        'alias' => 'HOUSING_NO_ACCESS',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],
                    [
                        'term' => 1,
                        'title' => 'Letter',
                        'tag' => 'First Access',
                        'alias' => 'HOUSING_1_ACCESS',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],
                    [
                        'term' => 1,
                        'title' => 'Letter',
                        'tag' => 'Second Access',
                        'alias' => 'HOUSING_2_ACCESS',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],

                ]
            ],
            [
                'title' => 'Appointment letter',
                'templates' => [
                    [
                        'term' => 1,
                        'title' => 'Letter',
                        'tag' => null,
                        'alias' => 'APPOINTMENT_LETTER',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 0,
                    ],
                ]
            ],
        ];

        foreach ($append as $parent_template) {
            $pt = \App\TemplateParent::create(['title' => $parent_template['title']]);
            foreach ($parent_template['templates'] as $template) {
                $t = \App\Templates::create($template);
                $pt->templates()->save($t);
            }
        }


    }
}
