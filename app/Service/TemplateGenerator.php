<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/23/18
 * Time: 1:02 PM
 */

namespace App\Service;

use CloudConvert\Api;
use Illuminate\Support\Facades\Config;
use App\Appointment;
use LynX39\LaraPdfMerger\PdfManage;

class TemplateGenerator
{
    public function fillAppoinmentLetterFromDocxTemplate($docx, Appointment $appointment)
    {
        set_time_limit(0);
        $docx_folder = Config::get('constants.storage_docx');
        $file = $docx_folder . '/' . $docx;
        if (!is_file($file)) {
            return false;
        }
        $template = new \PhpOffice\PhpWord\TemplateProcessor($file);
        $today = new \DateTime();
        $template->setValue('ContactName', $appointment->getContact());
        $template->setValue('Address', $appointment->address);
        $template->setValue('Address2', $appointment->state);
        $template->setValue('City', $appointment->city);
        $template->setValue('County', $appointment->country);
        $template->setValue('Postcode', $appointment->postcode);
        $template->setValue('TodayDate', $today->format('d/m/Y'));
        $template->setValue('JobID', $appointment->job_id);
        $template->setValue('ScheduleDate', $appointment->getFormatedScheduleDate());
        $template->setValue('ScheduleTime', $appointment->getFormatedScheduleDate() . ' ' . $appointment->getFormatedScheduleTime());
        $template->setValue('WorkType', $appointment->work_type);
        $new_file = md5(uniqid('generated_docx', true)) . '.docx';
        $template->saveAs($docx_folder . '/' . $new_file);
        return $new_file;
    }

    public function generatePdfFromDocx($docx)
    {
        $docx_folder = Config::get('constants.storage_docx');
        $pdf_folder = Config::get('constants.storage_pdf');
        $file = $docx_folder . '/' . $docx;
        if (!is_file($file)) {
            return false;
        }

        exec('libreoffice --headless --writer --convert-to pdf ' . $file . ' --outdir ' . $pdf_folder);
        $new_file = substr($docx, 0, -4) . 'pdf';
        return $new_file;
    }

    public function mergePdfs($pdf_names = [])
    {
        if (count($pdf_names) == 0) {
            return false;
        }
        $pdf_folder = Config::get('constants.storage_pdf') . '/';
        $pdf_merger = new PdfManage();
        foreach ($pdf_names as $pdf) {
            $file = $pdf_folder . $pdf;
            if (file_exists($file)) {
                $pdf_merger->addPDF($file, '1');
            }
        }
        $new_file = md5(uniqid('result_pdf', true)) . '.pdf';
        $pdf_merger->merge('file', $pdf_folder . $new_file);
        return $new_file;
    }

}