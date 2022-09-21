<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/23/18
 * Time: 1:02 PM
 */

namespace App\Service;

use App\Models\Appointment;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\HousingJob;
use App\Models\Templates;
use Illuminate\Support\Facades\Config;
use LynX39\LaraPdfMerger\PdfManage;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateGenerator
{
    public function fillAppoinmentLetterFromDocxTemplate($docx, Appointment $appointment = null)
    {
        set_time_limit(0);
        $docx_folder = Config::get('constants.storage_docx');
        $file = $docx_folder.'/'.$docx;

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
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $today = $day.' '.$month.' '.$year;


        $ContactName = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($appointment->getContact())))));
        $Address = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($appointment->getAddress()))));
        $exploded = explode(',', $Address);
        $address = htmlspecialchars((array_shift($exploded) ?? ''));
        $address2 = htmlspecialchars((trim(implode(', ', $exploded))));

        $state = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($appointment->state)))));
        $City = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($appointment->city)))));
        $County = htmlspecialchars((str_replace("\n", ', ', strtoupper($appointment->country))));
        $Postcode = htmlspecialchars((str_replace("\n", ', ', strtoupper($appointment->postcode))));
        $TodayDate = htmlspecialchars(($today));
        $JobID = htmlspecialchars(($appointment->job_id));
        $ScheduleDate = htmlspecialchars(($appointment->getFormatedScheduleDate()));
        $ScheduleTime = htmlspecialchars(
            ($appointment->getFormatedScheduleDate().' '.$appointment->getFormatedScheduleTime())
        );
        $WorkType = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($appointment->work_type)))));

        $variables = [
            'ContactName',
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode',
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
        $new_file = $appointment->job_id.'.appointment.'.$today->format('Y-m-d').'.docx';

        if ($appointment->docx) {
            $old_file = $docx_folder.'/'.$appointment->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($appointment->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder.'/'.$appointment->pdf;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        $template->saveAs($docx_folder.'/'.$new_file);

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
        $file = $docx_folder.'/'.$docx;
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
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $today = $day.' '.$month.' '.$year;

        $yesterday = new \DateTime('-1 day');
        $month = $yesterday->format('F');
        $year = $yesterday->format('Y');
        $day = ltrim($yesterday->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $yesterday = $day.' '.$month.' '.$year;


        $siteAddress = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->address)))));
        $city = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->city)))));
        $state = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->state)))));
        $postcode = htmlspecialchars((str_replace("\n", ', ', strtoupper(strtolower($housing->postal_code)))));
        $siteContact = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->siteContact())))));
        $serviceType = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->job_name)))));
        $first_name = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->given_name)))));
        $family_name = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->family_name)))));
        $due_date = htmlspecialchars(
            (str_replace("\n", ', ', ucwords(strtolower($housing->getScheduleDateWithDay()))))
        );
        $company_name = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->company_name)))));
        $contactName = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->siteContact())))));
        $contactPhone = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($housing->getContactPhone())))));

        $variables = [
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode',
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
        $template->setValue('ContactName', $contactName);
        $template->setValue('ContactPhone', $contactPhone);


        $today = new \DateTime();
        $new_file = $housing->job_id.'.housing.'.$today->format('Y-m-d').'.docx';

        if ($housing->docx) {
            $old_file = $docx_folder.'/'.$housing->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($housing->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder.'/'.$housing->pdf;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        $template->saveAs($docx_folder.'/'.$new_file);

        return $new_file;
    }

    public function fillPrivateByCustomer(Customer $customer, $type, $date)
    {
        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');
        $week1Minus2Day = (clone $week1)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week4Minus2Day = (clone $week4)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week8Minus2Day = (clone $week8)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week1 = $week1->format('Y-m-d 23:59:59');
        $week4 = $week4->format('Y-m-d 23:59:59');
        $week8 = $week8->format('Y-m-d 23:59:59');

        $contractNum = [];
        $assets = [];
        $assets_id = [];
        $dates = [];
        $site = null;
        $startDate = $date.' 00:00:00';
        $endDate = $date.' 23:59:59';
        foreach ($customer->contracts as $contract) {
            $temp_end = $contract->end_date;

            if ($type == '1 wk') {
                $isValid = $contract->is_processed_1;
            } else {
                if ($type == '4 wks') {
                    $isValid = $contract->is_processed_4;
                } else {
                    if ($type == '8 wks') {
                        $isValid = $contract->is_processed_8;
                    } else {
                        continue;
                    }
                }
            }


            if ($temp_end >= $startDate && $temp_end <= $endDate && !$isValid) {
                $end_date = $temp_end ?: $endDate;
                $contractNum[] = ($contract->contract_no);
                $assets[] = htmlspecialchars($contract->name);
                $eD = $contract->end_date ? \DateTime::createFromFormat('Y-m-d H:i:s', $contract->end_date)->format(
                    'd/m/Y'
                ) : '';
                $sD = $contract->start_date ? \DateTime::createFromFormat('Y-m-d H:i:s', $contract->start_date)->format(
                    'd/m/Y'
                ) : '';
                $contract_dates = $sD.' -  '.$eD;
                $dates[] = htmlspecialchars(trim(trim($contract_dates, ' '), '-'));
            }
        }
        if (!isset($end_date)) {
            return;
        }
        $end_date = \DateTime::createFromFormat('Y-m-d H:i:s', $end_date)->format('Y-m-d');

        if ($end_date >= $week1Minus2Day && $end_date <= $week1) {
            $template = Templates::where('alias', '=', Templates::PRIVATE_1_WEEK)->first();
        } else {
            if ($week4Minus2Day && $end_date <= $week4) {
                $template = Templates::where('alias', '=', Templates::PRIVATE_4_WEEK)->first();
            } else {
                if ($week8Minus2Day && $end_date <= $week8) {
                    $template = Templates::where('alias', '=', Templates::PRIVATE_8_WEEK)->first();
                } else {
                    return false;
                }
            }
        }
        $docx = $template->docx;
        if (!$docx) {
            return false;
        }


        $docx_folder = Config::get('constants.storage_docx');
        $file = $docx_folder.'/'.$docx;
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
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $today = $day.' '.$month.' '.$year;


        $address = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->address)))));
        $address2 = '';
        $city = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->city)))));
        $state = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->state)))));
        $postcode = htmlspecialchars((str_replace("\n", ', ', strtoupper($customer->postal_code))));

        $addressProperty = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($site ? $site->address : ''))));
        $address2Property = '';
        $cityProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->city : '')))));
        $stateProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->state : '')))));
        $postcodeProperty = htmlspecialchars((str_replace("\n", ', ', strtoupper($site ? $site->postal_code : ''))));

        $contractNo = implode(' ', array_unique($contractNum));
        $contactName = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($customer->getName()))));
        $totalDue = htmlspecialchars($contract->value);
        $expiryDate = htmlspecialchars($contract->getExpireDate());
        $contractName = htmlspecialchars($contract->name);

        $customerId = htmlspecialchars($customer->company_id);
        $assetId = implode(', ', $assets_id);
        $makeModel = implode('</w:t><w:br/><w:t>', array_unique($assets));

        $dates = implode('</w:t><w:br/><w:t>', $dates);

        $variables = [
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode',
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
            'SiteAddress',
            'SiteAddress2',
            'SiteCity',
            'SiteCounty',
            'SitePostCode',
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
        $template->setValue('MakeModel', $makeModel);
        $template->setValue('ContractDates', $dates);
        $template->setValue('ContractName', $makeModel);
        $template->setValue('ContactName', $contactName);
        $template->setValue('TotalDue', $totalDue);
        $template->setValue('ExpiryDate', $expiryDate);
        $template->setValue('ContractNo', $contractNo);
        $template->setValue('CustomerID', $customerId);
        $template->setValue('AssetID', $assetId);
        $template->setValue('PlanType', $makeModel);
        $template->setValue('TodayDate', $today);

        $today = new \DateTime();
        $new_file = $customerId.'.'.str_replace(' ', '.', $type).'.'.$today->format('Y-m-d').'.docx';

        if ($contract->docx) {
            $old_file = $docx_folder.'/'.$contract->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($contract->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder.'/'.$contract->pdf;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        $template->saveAs($docx_folder.'/'.$new_file);

        return $new_file;
    }


    public function fillPrivateEmailByCustomer(Customer $customer, $type, $date, $html)
    {
        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');

        $contractNum = [];
        $assets = [];
        $assets_id = [];
        $dates = [];
        $site = null;
        $startDate = $date.' 00:00:00';
        $endDate = $date.' 23:59:59';
        foreach ($customer->contracts as $contract) {
            $temp_end = $contract->end_date;

            if ($type == '1 wk') {
                $isValid = $contract->is_processed_1;
            } else {
                if ($type == '4 wks') {
                    $isValid = $contract->is_processed_4;
                } else {
                    if ($type == '8 wks') {
                        $isValid = $contract->is_processed_8;
                    } else {
                        continue;
                    }
                }
            }

            if ($temp_end >= $startDate && $temp_end <= $endDate && !$isValid) {
                $end_date = $temp_end;
                $contractNum[] = ($contract->contract_no);
                $assets[] = htmlspecialchars($contract->name);
                $eD = $contract->end_date ? \DateTime::createFromFormat('Y-m-d H:i:s', $contract->end_date)->format(
                    'd/m/Y'
                ) : '';
                $sD = $contract->start_date ? \DateTime::createFromFormat('Y-m-d H:i:s', $contract->start_date)->format(
                    'd/m/Y'
                ) : '';
                $contract_dates = $sD.' -  '.$eD;
                $dates[] = htmlspecialchars(trim(trim($contract_dates, ' '), '-'));
            }
        }

        $today = new \DateTime();
        $month = $today->format('F');
        $year = $today->format('Y');
        $day = ltrim($today->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $today = $day.' '.$month.' '.$year;

        $address = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->address)))));
        $address2 = '';
        $city = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->city)))));
        $state = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->state)))));
        $postcode = htmlspecialchars((str_replace("\n", ', ', strtoupper($customer->postal_code))));

        $addressProperty = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($site ? $site->address : ''))));
        $address2Property = '';
        $cityProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->city : '')))));
        $stateProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->state : '')))));
        $postcodeProperty = htmlspecialchars((str_replace("\n", ', ', strtoupper($site ? $site->postal_code : ''))));

        $contractNo = implode(' ', array_unique($contractNum));
        $contactName = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($customer->getName()))));
        $totalDue = htmlspecialchars($contract->value);
        $expiryDate = htmlspecialchars($contract->getExpireDate());
        $contractName = htmlspecialchars($contract->name);

        $customerId = htmlspecialchars($customer->company_id);
        $assetId = implode(', ', $assets_id);
        $makeModel = implode('<br>', array_unique($assets));

        $dates = implode('<br>', $dates);


        $variables = [
            '${Address}',
            '${Address2}',
            '${City}',
            '${County}',
            '${Postcode}',
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
            $html = str_replace($v, $str, $html);
        }

        $variablesProperty = [
            '${SiteAddress}',
            '${SiteAddress2}',
            '${SiteCity}',
            '${SiteCounty}',
            '${SitePostCode}',
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
            $html = str_replace($v, $str, $html);
        }

        $html = str_replace('${MakeModel}', $makeModel, $html);
        $html = str_replace('${ContractName}', $makeModel, $html);
        $html = str_replace('${ContractDates}', $dates, $html);
        $html = str_replace('${ContactName}', $contactName, $html);
        $html = str_replace('${TotalDue}', $totalDue, $html);
        $html = str_replace('${ExpiryDate}', $expiryDate, $html);
        $html = str_replace('${ContractNo}', $contractNo, $html);
        $html = str_replace('${CustomerID}', $customerId, $html);
        $html = str_replace('${AssetID}', $assetId, $html);
        $html = str_replace('${PlanType}', $makeModel, $html);
        $html = str_replace('${TodayDate}', $today, $html);

        return $html;
    }

    public function fillPrivateTemplate(Contract $contract)
    {
        set_time_limit(0);

        $week1 = new \DateTime('+1 week');
        $week1Minus = clone $week1;
        $week1Minus->modify('-2 day');
        $week4 = new \DateTime('+4 week');
        $week4Minus = clone $week4;
        $week4Minus->modify('-2 day');
        $week8 = new \DateTime('+8 week');
        $week8Minus = clone $week8;
        $week8Minus->modify('-2 day');

        $end_date = $contract->getEndDate();
        if (!$end_date) {
            return false;
        }
        $end_date = $end_date->format('Y-m-d');
        if ($week1->format('Y-m-d') == $end_date) {
            $template = Templates::where('alias', '=', Templates::PRIVATE_1_WEEK)->first();
            $type = '1wk';
        } else {
            if ($week4->format('Y-m-d') == $end_date) {
                $template = Templates::where('alias', '=', Templates::PRIVATE_4_WEEK)->first();
                $type = '4wks';
            } else {
                if ($week8->format('Y-m-d') == $end_date) {
                    $template = Templates::where('alias', '=', Templates::PRIVATE_8_WEEK)->first();
                    $type = '8wks';
                } else {
                    return false;
                }
            }
        }
        $docx = $template->docx;
        if (!$docx) {
            return false;
        }

        $docx_folder = Config::get('constants.storage_docx');
        $file = $docx_folder.'/'.$docx;
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
        } else {
            if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else {
                if ($day % 10 == 3 && $day != 13) {
                    $day .= 'rd';
                } else {
                    $day .= 'th';
                }
            }
        }
        $today = $day.' '.$month.' '.$year;


        $customer = $contract->customer()->first();
        $asset = $contract->assets()->first();
        $site = $asset ? $asset->site()->first() : null;

        $address = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->address)))));
        $address2 = '';
        $city = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->city)))));
        $state = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($customer->state)))));
        $postcode = htmlspecialchars((str_replace("\n", ', ', strtoupper($customer->postal_code))));

        $addressProperty = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($site ? $site->address : ''))));
        $address2Property = '';
        $cityProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->city : '')))));
        $stateProperty = htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($site ? $site->state : '')))));
        $postcodeProperty = htmlspecialchars((str_replace("\n", ', ', strtoupper($site ? $site->postal_code : ''))));

        $contractNo = implode(' ', array_unique($con));
        $makeModel = htmlspecialchars($asset ? $asset->value : 'Heating Equipment');
        $contactName = htmlspecialchars(str_replace("\n", ', ', ucwords(strtolower($customer->getName()))));
        $totalDue = htmlspecialchars($contract->value);
        $expiryDate = htmlspecialchars($contract->getExpireDate());
        $contractName = htmlspecialchars($contract->name);

        $customerId = $customer->company_id;
        $assetId = $asset ? $asset->asset_id : '';

        $variables = [
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode',
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
            'SiteAddress',
            'SiteAddress2',
            'SiteCity',
            'SiteCounty',
            'SitePostCode',
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
        $template->setValue('MakeModel', $makeModel);
        $template->setValue('ContractName', $contractName);
        $template->setValue('ContactName', $contactName);
        $template->setValue('TotalDue', $totalDue);
        $template->setValue('ExpiryDate', $expiryDate);
        $template->setValue('ContractNo', $contractNo);
        $template->setValue('CustomerID', $customerId);
        $template->setValue('AssetID', $assetId);
        $template->setValue('PlanType', $makeModel);
        $template->setValue('TodayDate', $today);

        $today = new \DateTime();
        $new_file = $customerId.'.'.$type.'.'.$today->format('Y-m-d').'.docx';

        if ($contract->docx) {
            $old_file = $docx_folder.'/'.$contract->docx;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        if ($contract->pdf) {
            $pdf_folder = Config::get('constants.storage_pdf');
            $old_file = $pdf_folder.'/'.$contract->pdf;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        $template->saveAs($docx_folder.'/'.$new_file);

        return $new_file;
    }

    public function generatePdfFromDocx($docx)
    {
        $docx_folder = Config::get('constants.storage_docx');
        $pdf_folder = Config::get('constants.storage_pdf');
        $file = $docx_folder.'/'.$docx;
        if (!is_file($file)) {
            return false;
        }
        exec(
            Config::get(
                'constants.libreoffice'
            ).' --headless --writer --convert-to pdf:writer_pdf_Export '.$file.' --outdir '.$pdf_folder
        );
        $new_file = substr($docx, 0, -4).'pdf';

        return $new_file;
    }

    public static function sGeneratePdfFromDocx($docx)
    {
        $docxFolder = Config::get('constants.storage_docx');
        $pdfFolder = Config::get('constants.storage_pdf');
        $file = $docxFolder.'/'.$docx;
        if (!is_file($file)) {
            return false;
        }
        shell_exec(
            Config::get(
                'constants.libreoffice'
            ).' --headless --writer --convert-to pdf:writer_pdf_Export '.$file.' --outdir '.$pdfFolder
        );
        sleep(1.5);
        $newFile = substr($docx, 0, -4).'pdf';

        return $newFile;
    }

    public function mergePdfs($pdf_names = [])
    {
        if (count($pdf_names) == 0) {
            return false;
        }
        $pdf_folder = Config::get('constants.storage_pdf').'/';
        $pdf_merger = new PdfManage();;
        $name = 'temp';
        foreach ($pdf_names as $pdf) {
            if ($pdf instanceof HousingJob) {
                $name = 'housing';
                $file = $pdf_folder.$pdf->pdf;
                if ($pdf->tags == 'No Access 3 (Letter)') {
                    $pages = '1-2';
                } else {
                    $pages = '1';
                }
            } else {
                if ($pdf instanceof Contract) {
                    $file = $pdf_folder.$pdf->pdf;
                    $pages = '1-2';
                    $name = 'private';
                } else {
                    if (is_array($pdf)) {
                        $file = $pdf_folder.$pdf['pdf'];
                        $pages = $pdf['page'];
                        $name = 'private';
                    } else {
                        $file = $pdf_folder.$pdf;
                        $pages = '1';
                        $name = 'appointment';
                    }
                }
            }
            if (file_exists($file)) {
                $pdf_merger->addPDF($file, $pages);
            }
        }
        $today = new \DateTime();
        $new_file = $name.$today->format('Y-m-d.H-i-s').'.pdf';
        $pdf_merger->merge('file', $pdf_folder.$new_file);

        return $new_file;
    }

    public static function mergeAllPdfsPages($pdfs, $name)
    {
        $pdfFolder = Config::get('constants.storage_pdf').'/';
        $pdfMerger = new PdfManage();
        foreach ($pdfs as $pdf) {
            $file = $pdfFolder.$pdf;
            if (file_exists($file)) {
                $pdfMerger->addPDF($file);
            } else {
                dump('no file '.$file);
            }
        }
        $pdfMerger->merge('file');
        $pdfMerger->save($pdfFolder.$name);

        return $name;
    }

    public function mergeProvidedPdfs($pdf_names = [])
    {
        if (count($pdf_names) == 0) {
            return false;
        }

        $pdf_folder = Config::get('constants.storage_pdf').'/';
        $pdf_merger = new PdfManage();
        $name = 'report.';
        foreach ($pdf_names as $pdf) {
            $file = $pdf_folder.$pdf;
            $pdf_merger->addPDF($file);
        }

        $today = new \DateTime();
        $new_file = $name.$today->format('Y-m-d.H-i-s').'.pdf';
        $pdf_merger->merge('file', $pdf_folder.$new_file);

        return $new_file;
    }

    public function removeProvidedPdfs($pdf_names = [])
    {
        $pdf_folder = Config::get('constants.storage_pdf').'/';
        foreach ($pdf_names as $pdf) {
            $file = $pdf_folder.$pdf;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function removeProvidedHtmls($html_names = [])
    {
        $html_folder = Config::get('constants.storage_html').'/';
        foreach ($html_names as $html) {
            $file = $html_folder.$html;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }


    public static function getCorrectString($string)
    {
        return htmlspecialchars((str_replace("\n", ', ', ucwords(strtolower($string)))));
    }

    public static function getCorrectStringWithoutUcwords($string)
    {
        return htmlspecialchars((str_replace("\n", ', ', strtolower($string))));
    }

    public static function getCorrectStringUppercased($string)
    {
        return strtoupper(self::getCorrectString($string));
    }

    public static function getFloatValue($value)
    {
        return number_format((float)TemplateGenerator::getCorrectString($value), 2);
    }

    public static function fillValuesIfExistWithLimit(
        TemplateProcessor $templateProcessor,
        $variables,
        $values,
        $limit = 1
    ) {
        foreach ($variables as $v) {
            $str = '';
            while (($value = array_shift($values)) !== null) {
                if (strlen($value) > 0) {
                    $str = $value;
                    break;
                }
            }
            $templateProcessor->setValue($v, $str, $limit);
        }
    }

}
