<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 12:09 PM
 */

namespace App\Service\Upload;


use App\Appointment;
use App\Models\AppointmentProcessed;
use App\Service\simProRequestService;
use App\Service\simProService;
use Illuminate\Support\Facades\DB;

class JobUpload
{

    /** @var simProRequestService */
    private $simpro;

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }

    public function run()
    {
        DB::delete('DELETE FROM appointments WHERE id > 0;');
        $begin = new \DateTime('+2 day');
        $end = new \DateTime('+17 day');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);


        foreach ($period as $dt) {
            $result = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/schedules/?Type=job&Date=' . $dt->format('Y-m-d'));
            if ($result) {
                foreach ($result as $url) {
                    $this->getPageByNumber($url);
                }
            }
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
        dump('parseSchedule ' . $job_scheduler->ID);
        if (!isset($job_scheduler->Reference)) {
            dump('no reference');
            return;
        }
        $job_parse = explode('-', $job_scheduler->Reference);
        $job_id = array_shift($job_parse);
        dump('job id = ' . $job_id);
        $result_job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/' . $job_id . '?display=all');
        if (!$result_job) {
            dump('!$result_job');
            return;
        }
        $site_id = $result_job->Site->ID ?? null;
        if ($site_id) {
            $this->parseSite($job_scheduler, $result_job, $site_id);
        } else {
            dump('site_id null');
        }
    }

    public function parseSite($job_scheduler, $result_job, $site_id)
    {
        dump('parseSite ');
        $site = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site_id);
        if ($site) {
            $this->createAppointment($job_scheduler, $result_job, $site);
        }
    }

    /**
     * Создаем appointment относительно полученных данных
     */
    public function createAppointment($job_scheduler, $result_job, $site)
    {
        $date = $job_scheduler->Date;
        $blocks = $job_scheduler->Blocks;
        $time = $blocks[0]->StartTime;

        $jobId = $result_job->ID;
        $workType = $result_job->Sections[0]->CostCenters[0]->CostCenter->Name ?? null;
        $customerId = $result_job->Customer->ID;
        $sendDate = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time) ?? null;

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

        $appointment = Appointment::create([
            'customer_id' => $customerId,
            'title' => $title,
            'family_name' => $familyName,
            'given_name' => $givenName,
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
            'time' => $time
        ]);
    }


    /**
     * Очищаем дубликаты, оставляем только те appointment которые первые по времени
     */
    public function clearDublicates()
    {

        DB::insert('INSERT INTO appointments_logged (job_id, send_date, site_id)
SELECT job_id, send_date, site_id
FROM appointments');


        $begin = new \DateTime('+2 day');
        $end = new \DateTime('+17 day');

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
    }
}