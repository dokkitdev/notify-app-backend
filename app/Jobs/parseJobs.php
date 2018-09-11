<?php

namespace App\Jobs;

use App\Service\simProService;
use App\SimProJobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class parseJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $url;
    private $companyId;
    private $jobId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url,$companyId,$jobId)
    {
        $this->url=$url;
        $this->companyId=$companyId;
        $this->jobId=$jobId;
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
        $job=$this->getJob($this->jobId);
        $simProservice=new simProService();
        $parsedJob=$simProservice->parseJobByUrl($this->url);
        $job->parsedData=json_encode($parsedJob);
        $job->simpro_id=$this->jobId;
        $job->save();

    }
    private function getJob($simpro_id){
        if($simpro_id)$job=SimProJobs::where(['simpro_id'=>$simpro_id])->get()->toArray();
        if($simpro_id&&isset($job[0]['id']))return SimProJobs::find($job[0]['id']);
        else return new SimProJobs();
    }
}
