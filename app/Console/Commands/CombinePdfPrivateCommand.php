<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Models\Contract;
use App\Models\HousingJob;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class CombinePdfPrivateCommand extends Command
{
    protected $signature = 'combine:pdf:private {combined} {log}';
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
            $contract = Contract::find($c);
            $docx = $generator->fillPrivateTemplate($contract);
            $pdf = $generator->generatePdfFromDocx($docx);
            $contract->docx = $docx;
            $contract->pdf = $pdf;
            $contract->setProcess();
            $contract->save();
//            $sim->uploadAppointment($appointment);
            $filled[] = $contract;
        }
        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            $log->pdf = $merged;
        }
        $log->is_finished = 1;
        $log->save();


    }
}