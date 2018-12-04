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

    public function uploadJob()
    {
        DB::delete('DELETE FROM `appointments` WHERE id > 0;');
            $begin = new \DateTime();
        $end = new \DateTime('+14 day');

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
    }

    public function getPageByNumber($url)
    {
        $schedules = $this->simpro->getRequest('get', $url);
        foreach ($schedules as $job_scheduler) {
            $this->parseSchedule($job_scheduler);
        }
    }

    public function parseSchedule($job_scheduler)
    {
        if (!isset($job_scheduler->Reference)) {
            return;
        }
        $job_parse = explode('-', $job_scheduler->Reference);
        $job_id = array_shift($job_parse);

        if (count(AppointmentProcessed::where('job_id', '=', $job_id)->get()) > 0) {
            return;
        }
        $result_job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/' . $job_id);
        if (!$result_job) {
            return;
        }
        $site_id = $result_job->Site->ID ?? null;
        if ($site_id) {
            $this->parseSite($job_scheduler, $result_job, $site_id);
        }
    }

    public function parseSite($job_scheduler, $result_job, $site_id)
    {
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
        $this->clearDublicates();
    }


    /**
     * Очищаем дубликаты, оставляем только те appointment которые первые по времени
     */
    public function clearDublicates()
    {
        $result = DB::select('SELECT job_id, min(`send_date`) as mtime FROM appointments GROUP BY job_id HAVING COUNT(*) > 1');
        foreach ($result as $r) {

            $appointment = Appointment::where('job_id', '=', $r->job_id)
                ->where('is_proccessed', '=', 1)
                ->orderBy('send_date', 'ASC')
                ->first();
            if ($appointment) {
                $id = $appointment->id;
            } else {
                $finded = DB::select('SELECT id FROM appointments WHERE job_id = :job and `send_date` = :send_date LIMIT 1', [
                    $r->job_id,
                    $r->mtime
                ]);
                $id = $finded[0]->id;
            }
            DB::delete('DELETE FROM `appointments` WHERE job_id = :job AND id <> :id;', [
                $r->job_id,
                $id
            ]);
        }
    }
}