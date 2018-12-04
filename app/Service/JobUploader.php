<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/28/18
 * Time: 12:53 PM
 */

namespace App\Service;


use App\Appointment;
use App\Jobs\AppointmentJob;
use App\Jobs\AppointmentPage;
use App\Jobs\AppointmentSite;
use App\Models\AppointmentProcessed;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;

class JobUploader
{
    private $requester;

    public function __construct()
    {
        $this->requester = new Requester();
    }


    /**
     * Стартуем подтягивание jobs
     * Проверяем количество страниц
     * и запускаем парсеры
     */
    public function run()
    {
        $promise = $this->requester->getRequestAsync('companies/0/schedules/', [
            'Type' => 'job',
            'display' => 'all',
            'pageSize' => 1,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $headers = $res->getHeaders();
                if (array_key_exists('Result-Total', $headers)) {
                    $pages = (int)ceil($headers['Result-Total'][0] / 25);
                    for ($i = $pages; $i > 0; $i--) {
//                        dump('page ' . $i);
                        $this->getPageWithJobs($i);
                        dispatch(new AppointmentPage($i));
                    }
                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
        $this->clearDublicates();
    }

    /**
     * Получаем jobs на определенной странице
     * и запускаем парсер для каждой работы
     */
    public function getPageWithJobs($page = 1)
    {
        $promise = $this->requester->getRequestAsync('companies/0/schedules/', [
            'Type' => 'job',
            'display' => 'all',
            'pageSize' => 25,
            'page' => $page,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $result = json_decode($res->getBody()->getContents());
                foreach ($result as $job) {
//                    dump('add parse');
//                    $this->addParseJob($job);
                    dispatch(new AppointmentJob($job));
                }
            },
            function (RequestException $e) {
                echo 'getPageWithJobs error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
//        $promise->wait();
    }

    /**
     * Получаем полную информацию о job,
     * проверяем есть ли у нее site
     * и если есть продолжаем парсить site
     */
    public function addParseJob($job_scheduler)
    {

        $job_parse = explode('-', $job_scheduler->Reference);
        $job_id = array_shift($job_parse);

        if (count(AppointmentProcessed::where('job_id', '=', $job_id)->get()) > 0) {
            return true;
        }

        $promise = $this->requester->getRequestAsync('companies/0/jobs/' . $job_id, [
            'display' => 'all',
        ]);

        $promise->then(
            function (ResponseInterface $res) use ($job_scheduler) {
                $result_job = json_decode($res->getBody()->getContents());
                $site_id = $result_job->Site->ID ?? null;
                if ($site_id) {
//                    dump('finishParseJob');
                    dispatch(new AppointmentSite($job_scheduler, $result_job, $site_id));
//                    $this->finishParseJob($job_scheduler, $result_job, $site_id);
                }
            },
            function (RequestException $e) {
                echo 'addParseJob error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
    }

    /**
     * Получаем site после чего запускаем создание Appointment
     */
    public function finishParseJob($job_scheduler, $result_job, $site_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $site_id, [
            'display' => 'all',
        ]);

        $promise->then(
            function (ResponseInterface $res) use ($job_scheduler, $result_job) {
                $site = json_decode($res->getBody()->getContents());
                $this->createAppointment($job_scheduler, $result_job, $site);
            },
            function (RequestException $e) {
                echo 'finishParseJob error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
    }

    /**
     * Создаем appointment относительно полученных данных
     */
    public function createAppointment($job_scheduler, $result_job, $site)
    {
        $date = $job_scheduler->Date;
        $blocks = $job_scheduler->Blocks;
        $time = $blocks[0]->EndTime;

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