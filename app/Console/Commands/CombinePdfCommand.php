<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class CombinePdfCommand extends Command
{
    protected $signature = 'combine:pdf {combined}';
    protected $description = 'Combine pdfs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(0);
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
        }
        Logs::create([
            'customer_type' => 'Appointment',
            'letters_generated' => count($filled),
            'email_generated' => 0,
            'pdf' => $merged
        ]);
    }
}