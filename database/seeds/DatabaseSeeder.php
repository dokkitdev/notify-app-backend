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
                'templates' => [
                    [
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
                        'term' => null,
                        'title' => 'Letter',
                        'tag' => 'No Access 1 (Letter)',
                        'alias' => 'HOUSING_NO_ACCESS',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],
                    [
                        'term' => null,
                        'title' => 'Letter',
                        'tag' => 'No Access 2 (Letter)',
                        'alias' => 'HOUSING_1_ACCESS',
                        'html_body' => null,
                        'html' => null,
                        'subject' => null,
                        'docx' => null,
                        'pdf' => null,
                        'is_html' => 1,
                    ],
                    [
                        'term' => null,
                        'title' => 'Letter',
                        'tag' => 'No Access 3 (Letter)',
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
                        'term' => null,
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
        \Illuminate\Support\Facades\DB::statement("
        INSERT INTO users
(id,
name,
email,
password,
role,
active,
remember_token,
created_at,
updated_at)
VALUES (
1,
'Admin',
'omenpars@gmail.com',
'$2y$10$0C4/Er6Txp3wxqz34zEVJuIXzW1I5nuAb7sC/ZBTY2jGjRRto/nW.',
'admin',
1,
'6Adou9HLG5lTBPPwLG12qMr1iJ5i6TeWD37JLaFsbv7uRxoBPwcdvEcWr58u',
'2018-09-13 08:58:52',
'2018-09-13 08:58:52');
        ");
    }
}



















