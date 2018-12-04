<?php

namespace App\Console\Commands;

use App\Service\PrivateUploader;
use App\Service\simProRequestService;
use App\Service\Upload\PrivateUpload;
use Illuminate\Console\Command;

class PrivateCommand extends Command
{
    protected $signature = 'upload:private';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        (new PrivateUpload())
            ->run();
    }

//    public function q()
//    {
//        $sim = new simProRequestService();
//        $companies = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/companies/');
//        foreach ($companies as $companyId) {
//            $company = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/companies/' . $companyId->ID);
//
//            $contracts = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/' . $companyId->ID . '/contracts/');
//            $contracts_id = [];
//            foreach ($contracts as $contractId) {
//                $contract = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/' . $companyId->ID . '/contracts/' . $contractId->ID);
//                $contracts_id[] = $contractId->ID;
//            }
//            if (sizeOf($contracts) < 1) {
//                continue;
//            }
//
//            //create customer
//            //create contracts
//
//            if (isset($company->Sites)) {
//                foreach ($company->Sites as $siteId) {
//                    $assets = $sim->getRequest('GET', '/api/v1.0/companies/0/sites/' . $siteId->ID . '/assets/?CustomerContract ne()');
//                    foreach ($assets as $assetId) {
//                        $asset = $sim->getRequest('GET', '/api/v1.0/companies/0/sites/' . $siteId->ID . '/assets/' . $assetId->ID);
//                        if (sizeOf($asset->CustomerContract) < 1) {
//                            continue;
//                        }
//                    }
//                }
//            }
//
//        }
//        die;
//    }
}
