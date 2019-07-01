<?php

namespace App\Jobs;

use App\Service\HousingUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HousingPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $page_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page_id)
    {
        $this->page_id = $page_id;
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
            ->parseJobPage($this->page_id);
    }
}
