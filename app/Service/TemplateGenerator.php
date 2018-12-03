<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/23/18
 * Time: 1:02 PM
 */

namespace App\Service;

use App\Models\HousingJob;
use App\Templates;
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
        $month = $today->format('F');
        $year = $today->format('Y');
        $day = ltrim($today->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else if ($day % 10 == 2 && $day != 12) {
            $day .= 'nd';
        } else if ($day % 10 == 3 && $day != 13) {
            $day .= 'rd';
        } else {
            $day .= 'th';
        }
        $today = $day . ' ' . $month . ' ' . $year;


        $ContactName = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->getContact()))));
        $Address = str_replace("\n", ', ', ucwords(strtolower($appointment->getAddress())));
        $exploded = explode(',', $Address);
        $address = htmlentities(array_shift($exploded) ?? '');
        $address2 = htmlentities(trim(implode(', ', $exploded)));

        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->state))));
        $City = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->city))));
        $County = htmlentities(str_replace("\n", ', ', strtoupper($appointment->country)));
        $Postcode = htmlentities(str_replace("\n", ', ', strtoupper($appointment->postcode)));
        $TodayDate = htmlentities($today);
        $JobID = htmlentities($appointment->job_id);
        $ScheduleDate = htmlentities($appointment->getFormatedScheduleDate());
        $ScheduleTime = htmlentities($appointment->getFormatedScheduleDate() . ' ' . $appointment->getFormatedScheduleTime());
        $WorkType = htmlentities(str_replace("\n", ', ', ucwords(strtolower($appointment->work_type))));

        $variables = [
            'ContactName',
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode'
        ];

        $values = [
            $ContactName,
            $address,
            $address2,
            $City,
            $state,
            $Postcode,
        ];

        foreach ($variables as $v) {
            $str = '';
            while (($value = array_shift($values)) !== null) {
                if (strlen($value) > 0) {
                    $str = $value;
                    break;
                }
            }
            $template->setValue($v, $str);
        }


        $template->setValue('TodayDate', $TodayDate);
        $template->setValue('JobID', $JobID);
        $template->setValue('ScheduleDate', $ScheduleDate);
        $template->setValue('ScheduleTime', $ScheduleTime);
        $template->setValue('WorkType', $WorkType);

        $today = new \DateTime();
        $new_file = $appointment->job_id . '.appointment.' . $today->format('Y-m-d') . '.docx';

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

    private $housingTemplates;

    public function fillHousingTemplate(HousingJob $housing)
    {

        if (!$this->housingTemplates) {
            $this->housingTemplates = [
                'No Access 1 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_NO_ACCESS)->first(),
                'No Access 2 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_1_ACCESS)->first(),
                'No Access 3 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_2_ACCESS)->first(),
            ];
        }

        $docx = $this->housingTemplates[$housing->tags]->docx;

        set_time_limit(0);
        $docx_folder = Config::get('constants.storage_docx');
        $file = $docx_folder . '/' . $docx;
        if (!is_file($file)) {
            return false;
        }
        $template = new \PhpOffice\PhpWord\TemplateProcessor($file);
        $today = new \DateTime();
        $month = $today->format('F');
        $year = $today->format('Y');
        $day = ltrim($today->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else if ($day % 10 == 2 && $day != 12) {
            $day .= 'nd';
        } else if ($day % 10 == 3 && $day != 13) {
            $day .= 'rd';
        } else {
            $day .= 'th';
        }
        $today = $day . ' ' . $month . ' ' . $year;


        $siteAddress = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->address))));
        $city = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->city))));
        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->state))));
        $postcode = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->postal_code))));
        $siteContact = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->siteContact()))));
        $serviceType = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->job_name))));
        $first_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->given_name))));
        $family_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->family_name))));
        $due_date = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->getDueDateWithDay()))));
        $company_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->company_name))));

        $variables = [
            'SiteAddress',
            'Address2',
            'City',
            'County',
            'County',
            'Postcode'
        ];

        $values = [
            $siteAddress,
            '',
            $city,
            $state,
            $postcode,
        ];

        foreach ($variables as $v) {
            $str = '';
            while (($value = array_shift($values)) !== null) {
                if (strlen($value) > 0) {
                    $str = $value;
                    break;
                }
            }
            $template->setValue($v, $str);
        }

        $template->setValue('TodayDate', $today);
        $template->setValue('FirstName', $first_name);
        $template->setValue('LastName', $family_name);
        $template->setValue('JobNumber', $housing->job_id);
        $template->setValue('SiteContact', $siteContact);
        $template->setValue('ServiceType', $serviceType);
        $template->setValue('DueDate', $due_date);
        $template->setValue('HousingCompany', $company_name);


        $today = new \DateTime();
        $new_file = $housing->job_id . '.housing.' . $today->format('Y-m-d') . '.docx';

        if ($housing->docx) {
            $old_file = $docx_folder . '/' . $housing->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($housing->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder . '/' . $housing->pdf;
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
            if ($pdf instanceof HousingJob) {
                $file = $pdf_folder . $pdf->pdf;
                if ($pdf->tags == 'No Access 1 (Letter)') {
                    $pages = '1';
                } else {
                    $pages = '1-2';
                }
            } else {
                $file = $pdf_folder . $pdf;
                $pages = '1';
            }
            if (file_exists($file)) {
                dump($pages);
                $pdf_merger->addPDF($file, $pages);
            }
        }
        $today = new \DateTime();
        $new_file = 'appointments.' . $today->format('Y-m-d.H-i-s') . '.pdf';
        $pdf_merger->merge('file', $pdf_folder . $new_file);
        return $new_file;
    }

}