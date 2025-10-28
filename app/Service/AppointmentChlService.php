<?php


namespace App\Service;


use App\Helpers\Date;
use App\Models\Appointment;
use App\Models\AppointmentLogged;
use App\Models\AppointmentProcessed;
use App\Models\Logs;
use App\Models\Templates;
use Illuminate\Support\Facades\Config;

class AppointmentChlService
{
    private static $simPro;

    public static function getSimProService()
    {
        if (!self::$simPro) {
            self::$simPro = new simProRequestService();
        }
        return self::$simPro;
    }

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
        $templateProcessor->setValue('Time', $date->format('H:i'));

        $address = TemplateGenerator::getCorrectString($appointment->getAddress());
        $exploded = explode(',', $address);
        $address1 = array_shift($exploded) ?? '';
        $address2 = trim(implode(', ', $exploded));
        $values = [
            'Date' => Date::prettyFormatting($date),
            'Time' => $date->format('H:i'),
            'TodayDate' => Date::getFormattedToday(),
            'JobID' => $appointment->job_id,
            'JobNumber' => $appointment->job_id,
        ];


        foreach ($values as $key => $val) {
            $templateProcessor->setValue($key, $val);
        }

        $variables = [
            'ContactName',
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode'
        ];

        $values = [
            TemplateGenerator::getCorrectString($appointment->getContact()),
            $address1,
            $address2,
            TemplateGenerator::getCorrectString($appointment->city),
            TemplateGenerator::getCorrectString($appointment->state),
            TemplateGenerator::getCorrectStringUppercased($appointment->postcode),
        ];


        foreach ($variables as $v) {
            $str = '';
            while (($value = array_shift($values)) !== null) {
                if (strlen($value) > 0) {
                    $str = $value;
                    break;
                }
            }
            $templateProcessor->setValue($v, $str);
        }

        $appointments = [
            Templates::APPOINTMENT_LETTER_CHL_1 => null,
            Templates::APPOINTMENT_LETTER_CHL_2 => null,
            Templates::APPOINTMENT_LETTER_CHL_3 => null,
            Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_1 => null,
            Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_2 => null,
            Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_3 => null,
            Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_1 => null,
            Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_2 => null,
            Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_3 => null,
            Templates::APPOINTMENT_LETTER_GAS_CHL_1 => null,
            Templates::APPOINTMENT_LETTER_GAS_CHL_2 => null,
            Templates::APPOINTMENT_LETTER_GAS_CHL_3 => null,
        ];
        foreach ($appointments as $letterType => $val) {
            if ($appointment->letter_type == $letterType) {
                $appointments[$letterType] = $appointment;
                continue;
            }
            $appointments[$letterType] = AppointmentLogged::where('job_id', $appointment->job_id)
                ->where('site_id', $appointment->site_id)
                ->where('letter_type', $letterType)
                ->orderBy('id', 'desc')
                ->first();
        }
        self::getAppointmentLoggedTemplate($appointments, $appointment);
        $scheduleDate1 = $scheduleDate2 = $scheduleDate3 = '!NOT FOUND!';
        $dueDate = '';
        if ($appointment->first_date) {
            $scheduleDate1 = self::getFormatedScheduleDate($appointment->first_date, $appointment, true);
        }
        if ($appointment->second_date) {
            $scheduleDate2 = self::getFormatedScheduleDate($appointment->second_date, $appointment);
        }
        if ($appointment->third_date) {
            $scheduleDate3 = self::getFormatedScheduleDate($appointment->third_date, $appointment);
            $dueDate = self::getFormatedScheduleDate($appointment->first_date, $appointment);
        }

        $templateProcessor->setValue('ScheduleDate1', $scheduleDate1);
        $templateProcessor->setValue('ScheduleDate2', $scheduleDate2);
        $templateProcessor->setValue('ScheduleDate3', $scheduleDate3);
        $templateProcessor->setValue('DueDate', $dueDate);
        $templateProcessor->setValue('ServiceType', TemplateGenerator::getCorrectString($appointment->work_type));



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


    public static function getAppointmentLoggedTemplate(&$appointments, Appointment $appointment)
    {
        $letterType = $appointment->letter_type;

        $isSecondLetterAlright = $letterType == Templates::APPOINTMENT_LETTER_CHL_2
            && $appointments[Templates::APPOINTMENT_LETTER_CHL_1] != null;

        $isThirdLetterAlright = $letterType == Templates::APPOINTMENT_LETTER_CHL_3
            && $appointments[Templates::APPOINTMENT_LETTER_CHL_1] != null
            && $appointments[Templates::APPOINTMENT_LETTER_CHL_2] != null;

        if (
            $letterType == Templates::APPOINTMENT_LETTER_CHL_1
            || $isSecondLetterAlright
            || $isThirdLetterAlright
        ) {
            return;
        }
        $simproService = self::getSimProService();
        $result = $simproService->getRequest('GET', '/api/v1.0/companies/0/schedules/?Date=lt(' . $appointment->getYmd() . ')&Reference=' . $appointment->job_id . '%&Order by=Date');
        if (!$result || !count($result)) {
            return;
        }
        if ($letterType == Templates::APPOINTMENT_LETTER_CHL_2 && isset($result[0]->Date)) {
            $log1 = AppointmentLogged::create([
                'job_id' => $appointment->job_id,
                'send_date' => $result[0]->Date . ' 00:00:00',
                'site_id' => $appointment->site_id,
                'letter_type' => Templates::APPOINTMENT_LETTER_CHL_1,
            ]);
            $appointments[Templates::APPOINTMENT_LETTER_CHL_1] = $log1;
        } else if ($letterType == Templates::APPOINTMENT_LETTER_CHL_3 && count($result) > 0) {

            if ($appointments[Templates::APPOINTMENT_LETTER_CHL_2] == null) {
                $log2 = AppointmentLogged::create([
                    'job_id' => $appointment->job_id,
                    'send_date' => $result[0]->Date . ' 00:00:00',
                    'site_id' => $appointment->site_id,
                    'letter_type' => Templates::APPOINTMENT_LETTER_CHL_2,
                ]);
                $appointments[Templates::APPOINTMENT_LETTER_CHL_2] = $log2;
            }

            if ($appointments[Templates::APPOINTMENT_LETTER_CHL_1] == null) {
                $result = $result = $simproService->getRequest('GET', '/api/v1.0/companies/0/schedules/?Date=lt(' . $appointments[Templates::APPOINTMENT_LETTER_CHL_2]->getYmd() . ')&Reference=' . $appointment->job_id . '%&Order by=Date');
                if (!$result || !count($result)) {
                    return;
                }
                $log1 = AppointmentLogged::create([
                    'job_id' => $appointment->job_id,
                    'send_date' => $result[0]->Date . ' 00:00:00',
                    'site_id' => $appointment->site_id,
                    'letter_type' => Templates::APPOINTMENT_LETTER_CHL_1,
                ]);
                $appointments[Templates::APPOINTMENT_LETTER_CHL_1] = $log1;
            }
        }
    }

    public static function getFormattedScheduleDateForChl($currentAppointment, $letterAppointment)
    {
        $scheduleDate = '!NOT FOUND!';
        if ($letterAppointment == $currentAppointment) {
            $scheduleDate = $letterAppointment->getFormatedScheduleDate() . ' ' . $letterAppointment->getFormatedScheduleTime();
        } else {
            $scheduleDate = $letterAppointment->getFormatedScheduleDate();
        }
        return $scheduleDate;
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
            self::getSimProService()->uploadAppointmentChl($appointment);
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

    public static function getFormatedScheduleDate($date, $appointment, $isNeedToAddScheduleTime = false)
    {
        $date = $date ? \DateTime::createFromFormat('Y-m-d', $date) : null;
        if (!$date) {
            return '';
        }
        $weekday = $date->format('l');
        $month = $date->format('F');
        $year = $date->format('Y');
        $day = ltrim($date->format('d'), '0');
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

        $formatted = $weekday.', '.$day.' '.$month.' '.$year;

        if ($isNeedToAddScheduleTime) {
            $formatted .= ' '.$appointment->getFormatedScheduleTime();
        }

        return $formatted;
    }

}
