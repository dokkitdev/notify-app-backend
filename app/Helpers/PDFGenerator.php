<?php

namespace App\Helpers;

use Dompdf\Dompdf;

class PDFGenerator
{
    protected $dompdf;

    public function __construct()
    {
        $this->dompdf = new Dompdf();
        $this->dompdf->setPaper(env('PDF_LETTER_PAPER_SIZE', 'A4'), env('PDF_LETTER_PAPER_ORIENTATION', 'portrait'));
    }

    public function generatePDF($template, $data, $filename = 'letter.pdf', $subfolder = true)
    {
        foreach ($data as $key => $value)
            $template = str_replace($key, $value, $template);
        $this->dompdf->loadHtml($template);
        $this->dompdf->render();
        $content = $this->dompdf->output();
        $path = storage_path('app/pdf');
        if(!file_exists($path))
            mkdir($path);
        if($subfolder){
            $hash = md5(rand(0, 99999) . time());
            $path .= '/' . $hash;
            mkdir($path);
        }
        $path .= '/' . $filename;
        $result = file_put_contents($path, $content);
        return ($result === false ?: $path);
    }
}