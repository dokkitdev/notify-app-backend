<?php

namespace App\Jobs;

use App\Service\JobUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AppointmentPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $page;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page)
    {
        $this->page = $page;
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
            ->getPageWithJobs($this->page);
    }

    public function fail($exception = null)
    {
        $exception->getMessage();
    }
}
