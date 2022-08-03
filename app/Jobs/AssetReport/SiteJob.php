<?php

namespace App\Jobs\AssetReport;

use App\AssetLogForDev;
use App\Service\simProRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Service\Sender\Sender;

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
        if($site == 'done')
        {
            $this->sendDoneMail();
        }

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

        if ($siteId != '35590') {
            return;
        }

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
            dump($asset->ID);
            AssetJob::dispatch($this->site, $asset, $today);
        }
    }


    /**
     * Send mail if all sites done
     *
     * @return void
     */
    public function sendDoneMail()
    {
        Sender::send(
            'adminteam@blueflameheat.co.uk',
            'Asset Report',
            'The Asset Report is now ready to be processed'
        );

        Sender::send(
            'michelle@dokkit.co.uk',
            'Asset Report',
            'The Asset Report is now ready to be processed'
        );

        die;
    }
}
