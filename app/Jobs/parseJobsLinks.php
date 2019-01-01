<?php

namespace App\Jobs;

use App\Service\simProService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class parseJobsLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $url;
    private $companyId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url,$companyId)
    {
        $this->url=$url;
        $this->companyId=$companyId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->attempts()>5){ # удаление после 3-х не удачных попыток
            $this->delete();
        }

        $simProservice=new simProService();
        $parsedJobs=$simProservice->parseJobsLinks($this->url,$this->companyId);
    }
}
