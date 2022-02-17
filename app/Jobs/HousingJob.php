<?php

namespace App\Jobs;

use App\Service\HousingUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HousingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $job_info;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_info)
    {
        $this->job_info = $job_info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 5) {
            $this->delete();
        }
        (new HousingUploader())
            ->parseJob($this->job_info);
    }
}
