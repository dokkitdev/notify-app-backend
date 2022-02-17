<?php

namespace App\Jobs;

use App\Appointment;
use App\Service\JobUploader;
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

        (new JobUploader())
            ->finishParseJob($this->job_scheduler, $this->result_job, $this->site_id);

    }

    public function fail($exception = null)
    {
        $exception->getMessage();
    }
}
