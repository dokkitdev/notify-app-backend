<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/23/18
 * Time: 1:02 PM
 */

namespace App\Service;

use App\Models\Contract;
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

        $yesterday = new \DateTime('-1 day');
        $month = $yesterday->format('F');
        $year = $yesterday->format('Y');
        $day = ltrim($yesterday->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else if ($day % 10 == 2 && $day != 12) {
            $day .= 'nd';
        } else if ($day % 10 == 3 && $day != 13) {
            $day .= 'rd';
        } else {
            $day .= 'th';
        }
        $yesterday = $day . ' ' . $month . ' ' . $year;


        $siteAddress = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->address))));
        $city = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->city))));
        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->state))));
        $postcode = htmlentities(str_replace("\n", ', ', strtoupper(strtolower($housing->postal_code))));
        $siteContact = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->siteContact()))));
        $serviceType = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->job_name))));
        $first_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->given_name))));
        $family_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->family_name))));
        $due_date = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->getDueDateWithDay()))));
        $company_name = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->company_name))));
        $contactName = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->siteContact()))));
        $contactPhone = htmlentities(str_replace("\n", ', ', ucwords(strtolower($housing->getContactPhone()))));

        $variables = [
            'Address',
            'Address2',
            'City',
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
        $template->setValue('DueDate', $yesterday);
        $template->setValue('HousingCompany', $company_name);
        $template->setValue('ContactName', $contactName);
        $template->setValue('ContactPhone', $contactPhone);


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

    public function fillPrivateTemplate(Contract $contract)
    {
        set_time_limit(0);

        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');
        $end_date = $contract->getEndDate();
        if (!$end_date) {
            return false;
        }
        $end_date = $end_date->format('Y-m-d');
        if ($week1->format('Y-m-d') == $end_date) {
            $template = Templates::where('alias', '=', Templates::PRIVATE_1_WEEK)->first();
            $type = '1wk';
        } else if ($week4->format('Y-m-d') == $end_date) {
            $template = Templates::where('alias', '=', Templates::PRIVATE_4_WEEK)->first();
            $type = '4wks';
        } else if ($week8->format('Y-m-d') == $end_date) {
            $template = Templates::where('alias', '=', Templates::PRIVATE_8_WEEK)->first();
            $type = '8wks';
        } else {
            return false;
        }
        $docx = $template->docx;
        if (!$docx) {
            return false;
        }

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


        $customer = $contract->customer()->first();
        $asset = $contract->assets()->first();
        $site = $asset->site()->first();

        $address = str_replace("\n", ', ', ucwords(strtolower($customer->address)));
        $address2 = '';
        $city = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->city))));
        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->state))));
        $postcode = htmlentities(str_replace("\n", ', ', strtoupper($customer->postal_code)));

        $addressProperty = str_replace("\n", ', ', ucwords(strtolower($site->address)));
        $address2Property = '';
        $cityProperty = htmlentities(str_replace("\n", ', ', ucwords(strtolower($site->city))));
        $stateProperty = htmlentities(str_replace("\n", ', ', ucwords(strtolower($site->state))));
        $postcodeProperty = htmlentities(str_replace("\n", ', ', strtoupper($site->postal_code)));


        $contactName = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->getName()))));
        $contractNo = $contract->contract_id;
        $customerId = $customer->company_id;
        $assetId = $asset->asset_id;
        $planType = $asset->value;
        $totalDue = $contract->value;
        $expiryDate = $contract->getExpireDate();

        $variables = [
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode'
        ];

        $values = [
            $address,
            $address2,
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

        $variablesProperty = [
            'PropertyAddress',
            'PropertyAddress2',
            'PropertyCity',
            'PropertyCounty',
            'PropertyPostCode',
        ];

        $valuesProperty = [
            $addressProperty,
            $address2Property,
            $cityProperty,
            $stateProperty,
            $postcodeProperty,
        ];

        foreach ($variablesProperty as $v) {
            $str = '';
            while (($value = array_shift($valuesProperty)) !== null) {
                if (strlen($value) > 0) {
                    $str = $value;
                    break;
                }
            }
            $template->setValue($v, $str);
        }
//        die;

        $template->setValue('ContractNo', $contractNo);
        $template->setValue('CustomerID', $customerId);
        $template->setValue('AssetID', $assetId);
        $template->setValue('PlanType', $planType);
        $template->setValue('ContactName', $contactName);
        $template->setValue('TodayDate', $today);
        $template->setValue('TotalDue', $totalDue);
        $template->setValue('ExpiryDate', $expiryDate);

        $today = new \DateTime();
        $new_file = $customerId . '.' . $type . '.' . $today->format('Y-m-d') . '.docx';

        if ($contract->docx) {
            $old_file = $docx_folder . '/' . $contract->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($contract->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder . '/' . $contract->pdf;
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
        $name = 'temp';
        foreach ($pdf_names as $pdf) {
            if ($pdf instanceof HousingJob) {
                $name = 'housing';
                $file = $pdf_folder . $pdf->pdf;
                if ($pdf->tags == 'No Access 3 (Letter)') {
                    $pages = '1-2';
                } else {
                    $pages = '1';
                }
            } else if ($pdf instanceof Contract) {
                $file = $pdf_folder . $pdf->pdf;
                $pages = '1-2';
                $name = 'private';
            } else {
                $file = $pdf_folder . $pdf;
                $pages = '1';
                $name = 'appointment';
            }
            if (file_exists($file)) {
                $pdf_merger->addPDF($file, $pages);
            }
        }
        $today = new \DateTime();
        $new_file = $name . $today->format('Y-m-d.H-i-s') . '.pdf';
        $pdf_merger->merge('file', $pdf_folder . $new_file);
        return $new_file;
    }

}