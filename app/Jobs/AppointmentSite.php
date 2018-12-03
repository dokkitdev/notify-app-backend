<?php

namespace App\Jobs;

use App\Appointment;
use App\Service\Requester;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;

class AppointmentSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $job_scheduler;
    private $result_job;
    private $site_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_scheduler, $result_job, $site_id)
    {
        $this->job_scheduler = $job_scheduler;
        $this->result_job = $result_job;
        $this->site_id = $site_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 5) { # ttl
            $this->delete();
        }

        $requester = new Requester();

        $job_scheduler = $this->job_scheduler;
        $result_job = $this->result_job;
        $site_id = $this->site_id;
        $promise = $requester->getRequestAsync('companies/0/sites/' . $site_id, [
            'display' => 'all',
        ]);

        $promise->then(
            function (ResponseInterface $res) use ($job_scheduler, $result_job) {
                $site = json_decode($res->getBody()->getContents());

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

                Appointment::create([
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
            },
            function (RequestException $e) {
                echo 'finishParseJob error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $requester->tick();
        $promise->wait();
    }

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
