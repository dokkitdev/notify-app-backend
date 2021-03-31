<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 12:09 PM
 */

namespace App\Service\Upload;


use App\Appointment;
use App\Jobs\HousingJob;
use App\Models\AppointmentProcessed;
use App\Models\CostCenter;
use App\Models\ParsingLog;
use App\Service\simProRequestService;
use App\Service\simProService;
use App\Templates;
use Illuminate\Support\Facades\DB;

class JobUpload
{

    /** @var simProRequestService */
    private $simpro;

    const COASTLINE = 11514;
    private $totalCount;
    private $totalSuccess;
    private $reasons = [];
    private $ids = [];


    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }

    public function run()
    {
        DB::delete('DELETE FROM appointments WHERE id > 0;');
        $begin = new \DateTime('+3 day'); //+3 day
        $end = new \DateTime('+19 day');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);


        foreach ($period as $dt) {
            $result = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/schedules/?Type=job&Date=' . $dt->format('Y-m-d'));

            $this->totalCount = $this->simpro->result_count ?: 0;
            $this->totalSuccess = 0;
            $this->reasons = [];
            $this->ids = [];
            if ($result) {
                foreach ($result as $url) {
                    $this->getPageByNumber($url);
                }
            }
            ParsingLog::create(
                [
                    'type' => ParsingLog::APPOINTMENTS_CHL_TYPE,
                    'total_count' => $this->totalCount,
                    'total_success' => $this->totalSuccess,
                    'reasons' => $this->reasons,
                    'ids' => $this->ids,
                    'parsing_date' => $dt->format('Y-m-d'),
                ]
            );
        }
        $this->clearDublicates();
    }

    public function getPageByNumber($url)
    {
        dump('getPagyByBumber ' . $url);
        $schedules = $this->simpro->getRequest('get', $url);
        foreach ($schedules as $job_scheduler) {
            $this->parseSchedule($job_scheduler);
        }
    }

    public function parseSchedule($job_scheduler)
    {

        $this->ids[] = $job_scheduler->ID;
        dump('parseSchedule '.$job_scheduler->ID);
        if (!isset($job_scheduler->Reference)) {
            $this->reasons[] = 'Job "'.$job_scheduler->ID.'" has no Reference';
            dump('no reference');

            return;
        }
        $job_parse = explode('-', $job_scheduler->Reference);
        $job_id = array_shift($job_parse);
        dump('job id = '.$job_id);
        $result_job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/'.$job_id.'?display=all');
        if (!$result_job) {
            dump('!$result_job');

            return;
        }

        $site_id = $result_job->Site->ID ?? null;
        if (!$site_id) {
            $this->reasons[] = 'Job "'.$job_scheduler->ID.'". Site id is null';
            dump('site_id null');
            return;
        }

        $job_service = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/'.$job_id);
        if (!$job_service || $job_service->Type != 'Service') {
            return;
        }

        if ($result_job->Customer->ID == self::COASTLINE) {
            $tag = $this->selectTag($result_job);
            $letterType = $this->getLetterTypeByTagAndJobForChl($tag, $result_job);
            if (!$letterType) {
                $this->reasons[] = 'Job "'.$job_scheduler->ID.'" has cost center that is not in list';
                dump('Letter type is not valid!');

                return;
            }
            $this->parseSite($job_scheduler, $result_job, $site_id, Appointment::CHL_TYPE, $letterType);
        } else {
            $costCenter = null;
            $costCenterId = $result_job->Sections[0]->CostCenters[0]->CostCenter->ID ?? null;
            if ($costCenterId) {
                $costCenter = CostCenter::where('cost_center_id', $costCenterId)->first();
            }


            if (!$costCenter) {
                $this->reasons[] = 'Job "'.$job_scheduler->ID.'" has cost center that is not in list';
                dump('Letter type is not valid!');

                return;
            }

            dump('Service');
            $this->parseSite($job_scheduler, $result_job, $site_id);
        }
    }


    public function parseSite($job_scheduler, $result_job, $site_id, $type = Appointment::NORMAL_TYPE, $letterType = null)
    {
        dump('parseSite ');
        $site = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site_id);
        if ($site) {
            $this->createAppointment($job_scheduler, $result_job, $site, $type, $letterType);
        }
    }

    /**
     * Создаем appointment относительно полученных данных
     */
    public function createAppointment($job_scheduler, $result_job, $site, $type = Appointment::NORMAL_TYPE, $letterType = null)
    {
        dump($job_scheduler);

        $this->totalSuccess++;
        $date = $job_scheduler->Date;
        $blocks = $job_scheduler->Blocks;
        $time = $blocks[0]->StartTime;

        $jobId = $result_job->ID;

        $results = $this->simpro->getRequest(
            'get',
            '/api/v1.0/companies/0/schedules/?Reference='.$jobId.'-%&columns=Date'
        );
        $firstDate = $secondDate = $thirdDate = null;
        foreach ($results as $scheduleDate) {
            $scheduleDate = $scheduleDate->Date;
            if ($firstDate == null) {
                $firstDate = $scheduleDate;
            } elseif ($secondDate == null && $firstDate && $firstDate != $scheduleDate) {
                $secondDate = $scheduleDate;
            } elseif ($thirdDate == null && $secondDate && $secondDate != $scheduleDate) {
                $thirdDate = $scheduleDate;
            }

            if ($firstDate && $secondDate && $thirdDate) {
                break;
            }
        }

        $workType = $result_job->Sections[0]->CostCenters[0]->CostCenter->Name ?? null;
        $customerId = $result_job->Customer->ID;
        $companyName = $result_job->Customer->CompanyName;
        $sendDate = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time) ?? null;
        $dueDate = $result_job->DueDate ? (\DateTime::createFromFormat('Y-m-d', $result_job->DueDate) ?? null) : null;

        $siteId = $site->ID;
        $address = $site->Address->Address ?? null;
        $city = $site->Address->City ?? null;
        $state = $site->Address->State ?? null;
        $postalCode = $site->Address->PostalCode ?? null;
        $country = $site->Address->Country ?? null;
        $title = $site->PrimaryContact->Title ?? '';
        $givenName = $site->PrimaryContact->GivenName ?? '';
        $familyName = $site->PrimaryContact->FamilyName ?? '';

        if (strlen($title) == 0
            && strlen($givenName) == 0
            && strlen($familyName) == 0) {
            $title = 'The Occupier';
        }

        Appointment::create([
            'customer_id' => $customerId,
            'title' => $title,
            'family_name' => $familyName,
            'given_name' => $givenName,
            'company_name' => $companyName,
            'address' => $address,
            'state' => $state,
            'city' => $city,
            'country' => $country,
            'postcode' => $postalCode,
            'job_id' => $jobId,
            'send_date' => $sendDate,
            'work_type' => $workType,
            'appointment_id' => $jobSchedule->ID ?? null,
            'site_id' => $siteId,
            'time' => $time,
            'type' => $type,
            'letter_type' => $letterType,
            'due_date' => $dueDate,
            'first_date' => $firstDate,
            'second_date' => $secondDate,
            'third_date' => $thirdDate,
        ]);
    }


    /**
     * Очищаем дубликаты, оставляем только те appointment которые первые по времени
     */
    public function clearDublicates()
    {

        DB::insert('INSERT INTO appointments_logged (job_id, send_date, site_id, letter_type)
SELECT job_id, send_date, site_id, letter_type
FROM appointments');


        $begin = new \DateTime('+2 day');
        $end = new \DateTime('+19 day');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);
        foreach ($period as $dt) {
            $sDate = $dt->format('Y-m-d 00:00:00');
            $eDate = $dt->format('Y-m-d 23:59:59');
            /** Clear already processed */
            $appointment = AppointmentProcessed::where('date', '>', $sDate)
                ->where('date', '<', $eDate)
                ->get();
            if (sizeOf($appointment) > 0) {
                foreach ($appointment as $a) {
                    DB::delete('DELETE FROM appointments WHERE job_id = :job AND  send_date > \' ' . $sDate . '\' AND send_date < \'' . $eDate . '\';', [
                        $a->job_id
                    ]);
                }
            }

            /** Clear duplicates for day */
            $result = DB::select('SELECT job_id, min(send_date) as mtime FROM appointments WHERE send_date > \'' . $sDate . '\' AND send_date < \'' . $eDate . '\' GROUP BY job_id HAVING COUNT(*) > 1 ');
            dump($sDate . ' ' . $eDate);
            dump($result);
            foreach ($result as $r) {
                $finded = DB::select('SELECT id FROM appointments WHERE job_id = :job and send_date = :send_date AND  send_date > \' ' . $sDate . '\' AND send_date < \'' . $eDate . '\'  LIMIT 1', [
                    $r->job_id,
                    $r->mtime
                ]);
                $id = $finded[0]->id;
                DB::delete('DELETE FROM appointments WHERE job_id = :job AND id <> :id AND  send_date > \' ' . $sDate . '\' AND send_date < \'' . $eDate . '\';', [
                    $r->job_id,
                    $id
                ]);
            }

            /** Clear sequence */
            $previousDay = clone $dt;
            $previousDay->modify('-1 day');
            $sPreviousDate = $previousDay->format('Y-m-d 00:00:00');
            $ePreviousDate = $previousDay->format('Y-m-d 23:59:59');
            $yesterday_jobs = DB::select('SELECT job_id FROM appointments_logged WHERE send_date > \' ' . $sPreviousDate . '\' AND send_date < \'' . $ePreviousDate . '\' GROUP BY job_id;');
            foreach ($yesterday_jobs as $job) {
                DB::delete('DELETE FROM appointments WHERE job_id = :job AND  send_date > \' ' . $sDate . '\' AND send_date < \'' . $eDate . '\';', [
                    $job->job_id,
                ]);
            }
        }

        while (1) {

            $totals = DB::select('
        SELECT job_id, send_date, site_id, letter_type, COUNT(id) as total FROM appointments_logged
group by job_id, send_date, site_id, letter_type
HAVING total > 1
LIMIT 200
;');
            if (!count($totals)) {
                break;
            }
            foreach ($totals as $row) {

                DB::delete(
                    'DELETE FROM appointments_logged
WHERE job_id = :job
AND send_date = \''.$row->send_date.'\'
AND site_id = :site_id
AND letter_type '.($row->letter_type ? ('= \''.$row->letter_type.'\'') : 'is null').'
LIMIT '.($row->total - 1).';',
                    [
                        $row->job_id,
                        $row->site_id,
                    ]
                );
            }
        }
    }


    public function selectTag($job)
    {
        $tag_selected = null;
        foreach ($job->Tags as $tags) {
            $tag = $tags->Name;
            if ($tag == HousingUpload::TAG_NO_ACCESSS_3
                || $tag == HousingUpload::TAG_NO_ACCESSS_2
                || $tag == HousingUpload::TAG_NO_ACCESSS_1) {

                if (!$tag_selected) {
                    $tag_selected = $tag;
                    continue;
                }

                if ($tag_selected == HousingUpload::TAG_NO_ACCESSS_3) {
                    continue;
                }

                if (
                    $tag_selected == HousingUpload::TAG_NO_ACCESSS_2
                    && $tag == HousingUpload::TAG_NO_ACCESSS_3
                ) {
                    $tag_selected = $tag;
                    continue;
                }
                $tag_selected = $tag;
            }
        }
        return $tag_selected;
    }

    public function getLetterTypeByTagAndJobForChl($tag, $job)
    {
        $costCenter = null;
        $letterType = null;
        $costCenterId = $job->Sections[0]->CostCenters[0]->CostCenter->ID ?? null;
        if ($costCenterId) {
            $costCenter = CostCenter::where('cost_center_id', $costCenterId)->first();
        }

        if ($costCenter && $costCenter->type == CostCenter::TYPE_ELECTRIC) {
            $letterType = Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_1;
            if ($tag == HousingUpload::TAG_NO_ACCESSS_2) {
                $letterType = Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_2;
            } else {
                if ($tag == HousingUpload::TAG_NO_ACCESSS_3) {
                    $letterType = Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_3;
                }
            }
        } elseif ($costCenter && $costCenter->type == CostCenter::TYPE_GAS) {
            $letterType = Templates::APPOINTMENT_LETTER_GAS_CHL_1;
            if ($tag == HousingUpload::TAG_NO_ACCESSS_2) {
                $letterType = Templates::APPOINTMENT_LETTER_GAS_CHL_2;
            } else {
                if ($tag == HousingUpload::TAG_NO_ACCESSS_3) {
                    $letterType = Templates::APPOINTMENT_LETTER_GAS_CHL_3;
                }
            }
        } elseif ($costCenter && $costCenter->type == CostCenter::TYPE_OTHER) {
            $letterType = Templates::APPOINTMENT_LETTER_CHL_1;
            if ($tag == HousingUpload::TAG_NO_ACCESSS_2) {
                $letterType = Templates::APPOINTMENT_LETTER_CHL_2;
            } else {
                if ($tag == HousingUpload::TAG_NO_ACCESSS_3) {
                    $letterType = Templates::APPOINTMENT_LETTER_CHL_3;
                }
            }
        }

        return $letterType;
    }
}
