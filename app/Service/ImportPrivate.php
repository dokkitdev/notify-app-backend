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
                $site = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/companies/' . $c->ID);
//                dump($sId->ID);
            }
            dump($company);
            dump($contracts);
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