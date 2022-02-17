<?php

namespace App\Jobs\AssetReport;

use App\AssetLogForDev;
use App\Service\simProRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    private $site;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $simpro = new simProRequestService();
        $siteId = $this->site->ID ?? 0;

        $da = new \DateTime('-1 day');
        $assets = $simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/customerAssets/?display=all&Site.ID=$siteId&pageSize=100&Archived=false&columns=ID,AssetType,CustomFields,LastTest,StartDate"
        );
        if (!$assets) {
            AssetLogForDev::create(
                [
                    'desc' => 'Empty assets - "/api/v1.0/companies/0/customerAssets/?display=all&Site.ID='.$siteId.'&pageSize=100"',
                ]
            );

            return;
        }

        $today = (new \DateTime('+1 day'))->format('Y-m-d');
        foreach ($assets as $asset) {
            AssetJob::dispatch($this->site, $asset, $today);
        }
    }

}
