<?php

namespace App\Jobs;

use App\Models\AppointmentProcessed;
use App\Service\JobUploader;
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

        (new JobUploader())
            ->addParseJob($this->job_scheduler);

    }

    public function fail($exception = null)
    {
        $exception->getMessage();
    }

}
