<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class CombinePdfAppointmentCommand extends Command
{
    protected $signature = 'combine:pdf {combined} {log}';
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

        $template = Templates::where('alias', '=', Templates::APPOINTMENT_LETTER)->first();
        if ($template->html === null) {
            return;
        }

        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        foreach ($combined as $c) {
            $appointment = Appointment::find($c);
            $docx = $generator->fillAppoinmentLetterFromDocxTemplate($template->docx, $appointment);
            $pdf = $generator->generatePdfFromDocx($docx);
            $appointment->docx = $docx;
            $appointment->pdf = $pdf;
            $appointment->is_proccessed = true;
            $appointment->save();
//            $sim->uploadAppointment($appointment);
            $filled[] = $appointment->pdf;
        }
        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            $log->pdf = $merged;
        }
        $log->is_finished = 1;
        $log->save();


    }
}