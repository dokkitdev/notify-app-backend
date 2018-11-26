<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/26/18
 * Time: 10:49 AM
 */

namespace App\Service;


class ImportPrivate
{
    private $simProRequest;

    public function __construct()
    {
        $this->simProRequest = new simProRequestService();
    }

    public function runCompanies()
    {
        $companies = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/companies/');
        foreach ($companies as $c) {
            $contracts = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/' . $c->ID . '/contracts/');
            if (count($contracts) < 1) {
                continue;
            }
            $company = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/companies/' . $c->ID);
            foreach ($company->Sites as $sId) {
                $sites = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID . '/assets/?CustomerContract ne()');
                if (count($sites) < 1) {
                    continue;
                }
                /** находим если у сайта есть соответствующие контракты */
                foreach ($sites as $site) {
                    if (!isset($site->CustomerContract->ID)) {
                        continue;
                    }
                    foreach ($contracts as $contId) {
                        if ($site->CustomerContract->ID == $contId->ID) {
                            dump('совпало', $site, $contId);
                            //https://blueflamecornwallltd.simprosuite.com/api/v1.0/companies/0/sites/37304
                            $siteDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $site->ID);
                            dump($siteDetails);
                            $siteAssetsDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $site->ID . '/assets/' . $site->ID);
                            dump($siteAssetsDetails);
                            //1043 harcoded
                            die;
                            //$site->ID
                        }
                    }
                }
//                dump($sId->ID);
            }
            dump('------------------------------------------------------------');
        }
    }

    public function runIndividuals()
    {
        $individuals = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/individuals/');
        foreach ($individuals as $c) {
            $contracts = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/' . $c->ID . '/contracts/');
            if (count($contracts) < 1) {
                continue;
            }
            $individual = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/individuals/' . $c->ID);
            dump($individual);
            dump($contracts);
            dump('------------------------------------------------------------');
        }

    }

}