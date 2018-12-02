<?php

namespace App\Jobs;

use App\Service\Requester;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Psr\Http\Message\ResponseInterface;

class AppointmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $job_scheduler;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_scheduler)
    {
        $this->job_scheduler = $job_scheduler;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->attempts() > 5){ # ttl
            $this->delete();
        }


        $requester = new Requester();

        $job_scheduler = $this->job_scheduler;
        $job_parse = explode('-', $job_scheduler->Reference);
        $job_id = array_shift($job_parse);

        $promise = $requester->getRequestAsync('companies/0/jobs/' . $job_id, [
            'display' => 'all',
        ]);

        $promise->then(
            function (ResponseInterface $res) use ($job_scheduler) {
                $result_job = json_decode($res->getBody()->getContents());
                $site_id = $result_job->Site->ID ?? null;
                if ($site_id) {
                    self::dispatch(new AppointmentSite($job_scheduler, $result_job, $site_id));
                }
            },
            function (RequestException $e) {
                echo 'addParseJob error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );

        $requester->tick();
        $promise->wait();
    }
}
