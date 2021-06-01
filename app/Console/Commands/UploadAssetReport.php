<?php

namespace App\Console\Commands;

use App\AssetLogForDev;
use App\Jobs\AssetReport\SiteJob;
use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use App\Service\simProRequestService;
use Illuminate\Console\Command;

class UploadAssetReport extends Command
{
    /** @var simProRequestService */
    protected $simpro;
    protected $signature = 'upload:asset:report';
    protected $description = 'Command description';

    const SITES_URL = '/api/v1.0/companies/0/sites/';

    public function __construct()
    {
        $this->simpro = new simProRequestService();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	return;
        AssetLogForDev::query()->truncate();
        AssetReportValidation::query()->truncate();
        AssetReport::query()->truncate();

        $pages = $this->simpro->getRequestPage(
            'get',
            self::SITES_URL.'?Customers.ID=11514&columns=ID,CustomFields&pageSize=100'
        );
        foreach ($pages as $page) {
            $sites = $this->simpro->getRequest('get', $page);
            foreach ($sites as $site) {
                SiteJob::dispatch($site);
            }
        }
    }
}

