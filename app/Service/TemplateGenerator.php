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


        $ContactName = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->getContact()))));
        $Address = str_replace("\n", ', ', ucwords(strtolower($appointment->getAddress())));
        $exploded = explode(',', $Address);
        $address = htmlentities(array_shift($exploded) ?? '');
        $address2 = htmlentities(trim(implode(', ', $exploded)));

        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->state))));
        $City = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->city))));
        $County = htmlentities(str_replace("\n", ', ', strtoupper($appointment->country)));
        $Postcode = htmlentities(str_replace("\n", ', ', strtoupper($appointment->postcode)));
        $TodayDate = htmlentities($today->format('d/m/Y'));
        $JobID = htmlentities($appointment->job_id);
        $ScheduleDate = htmlentities($appointment->getFormatedScheduleDate());
        $ScheduleTime = htmlentities($appointment->getFormatedScheduleDate() . ' ' . $appointment->getFormatedScheduleTime());
        $WorkType = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->work_type))));

        $template->setValue('ContactName', $ContactName);
        $template->setValue('Address', $address);
        $template->setValue('Address2', $address2);
        $template->setValue('City', $City);
        $template->setValue('County', $state);
        $template->setValue('Postcode', $Postcode);
        $template->setValue('TodayDate', $TodayDate);
        $template->setValue('JobID', $JobID);
        $template->setValue('ScheduleDate', $ScheduleDate);
        $template->setValue('ScheduleTime', $ScheduleTime);
        $template->setValue('WorkType', $WorkType);
        $new_file = md5(uniqid('generated_docx', true)) . '.docx';

        if ($appointment->docx) {
            $old_file = $docx_folder . '/' . $appointment->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($appointment->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder . '/' . $appointment->pdf;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

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

        exec('libreoffice --headless --writer --convert-to pdf:writer_pdf_Export ' . $file . ' --outdir ' . $pdf_folder);
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