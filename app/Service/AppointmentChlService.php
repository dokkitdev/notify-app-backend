<?php


namespace App\Service;


use App\Appointment;
use App\Helpers\Date;
use App\Logs;
use App\Models\AppointmentProcessed;
use Illuminate\Support\Facades\Config;
use App\Templates;

class AppointmentChlService
{
    public static function generateFilesForAppointment(Appointment $appointment)
    {
        $template = Templates::where('alias', '=', $appointment->letter_type)->first();
        if (!$template || !$template->docx) {
            throw new \InvalidArgumentException('Please fill "' . $template->title . '" template by docx');
        }
        $docx = self::generateDocxForAppointment($appointment, $template);
        $pdf = TemplateGenerator::sGeneratePdfFromDocx($docx);
        $appointment->pdf = $pdf;
        $appointment->docx = $docx;
        $appointment->save();
        return $pdf;
    }


    public static function generateDocxForAppointment(Appointment $appointment, Templates $template)
    {
        $docxFolder = Config::get('constants.storage_docx');
        $pdfFolder = Config::get('constants.storage_pdf');

        $file = $docxFolder . '/' . $template->docx;

        $date = $appointment->send_date;
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $date);

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);
        $templateProcessor->setValue('Date', Date::prettyFormatting($date));
        $templateProcessor->setValue('Time', $date->format('H:i'));

        $today = new \DateTime();
        $name = str_replace(' ', '-', strtolower($template->title));
        $name = $appointment->job_id . '.' . $name . '.' . $today->format('Y-m-d') . '.docx';

        if ($appointment->docx) {
            $oldFile = $docxFolder . '/' . $appointment->docx;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            $oldFile = $pdfFolder . '/' . $appointment->pdf;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        $source = $docxFolder . '/' . $name;
        $templateProcessor->saveAs($source);
        return $name;

    }

    public static function startProcessing($appointments, Logs $log)
    {
        $pdfs = [];
        foreach ($appointments as $appointment) {
            $appointment = Appointment::find($appointment);
            self::generateFilesForAppointment($appointment);
            $appointment->is_proccessed = 1;
            $appointment->save();
            $pdfs[] = $appointment->pdf;
            AppointmentProcessed::create([
                'job_id' => $appointment->job_id,
                'date' => $appointment->send_date,
            ]);
        }
        $today = new \DateTime();
        $file = 'appointments.' . $today->format('Y-m-d.H-i-s') . '.pdf';
        TemplateGenerator::mergeAllPdfsPages($pdfs, $file);
        $log->letters_generated = count($pdfs);
        $log->email_generated = 0;
        $log->pdf = $file;
        $log->save();
        return $file;
    }
}