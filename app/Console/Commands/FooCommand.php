<?php

namespace App\Console\Commands;


use App\Helpers\PDFGenerator;
use Illuminate\Console\Command;

class FooCommand extends Command
{
    protected $signature = 'foo';
    protected $description = 'Foo command';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(PDFGenerator $pdfGenerator)
    {
        $template = '<html><body>
            <p>{{ p }}</p>
            <div>{{ div }}</div>
            </body></html>';
        $data = [
            '{{ p }}' => 'paragraph',
            '{{ div }}' => 'area',
        ];
        $result = $pdfGenerator->generatePDF($template, $data);
        echo $result;
    }
}