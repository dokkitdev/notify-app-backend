<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Models\HousingJob;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class CombinePdfHousingCommand extends Command
{
    protected $signature = 'combine:pdf:housing {combined} {log}';
    protected $description = 'Combine pdfs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        set_time_limit(0);
        $log = $this->argument('log');
        $log = Logs::find($log);
        $log->is_started = 1;
        $log->is_finished = 0;
        $log->save();
        $combined = $this->argument('combined');
        $combined = explode(',', $combined);


        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        foreach ($combined as $c) {
            $housing = HousingJob::find($c);
            $docx = $generator->fillHousingTemplate($housing);
            $pdf = $generator->generatePdfFromDocx($docx);
            $housing->docx = $docx;
            $housing->pdf = $pdf;
            $housing->is_proccessed = true;
            $housing->save();
            $sim->uploadHousing($housing);
            $filled[] = $housing;
        }
        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            $log->pdf = $merged;
        }
        $log->is_finished = 1;
        $log->save();


    }
}