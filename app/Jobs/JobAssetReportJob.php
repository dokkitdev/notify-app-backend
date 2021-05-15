<?php

namespace App\Jobs;

use App\Service\simProRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobAssetReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobId = $this->data['reference']['jobID'];
        $simpro = new simProRequestService();

        $job = $simpro->getRequest('get', "/api/v1.0/companies/0/jobs/${jobId}?columns=Customer,Site");
        if (!$job) {
            return;
        }

        $siteId = $job->Site->ID;
        $customerAssets = $simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/customerAssets/?display=all&Site.ID=".$siteId
        );
        foreach ($customerAssets as $customerAsset) {
            $data = [
                'reference' => [
                    'siteID' => $siteId,
                    'assetID' => $customerAsset->ID,
                ],
            ];
            AssetReportJob::dispatch($data);
        }
    }
}
