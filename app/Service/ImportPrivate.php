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
                $sites_contracts = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID . '/assets/?CustomerContract ne()');
                if (count($sites_contracts) < 1) {
                    continue;
                }
                /** находим если у сайта есть соответствующие контракты */
                foreach ($sites_contracts as $site_contract) {
                    if (!isset($site_contract->CustomerContract->ID)) {
                        continue;
                    }
                    dump($site_contract);
                    foreach ($contracts as $contId) {
                        if ($site_contract->CustomerContract->ID == $contId->ID) {
                            dump('совпало', $site_contract, $contId);
                            $contractNeeded = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/' . $c->ID . '/contracts/' . $contId->ID);
                            dump($contractNeeded);
                            //https://blueflamecornwallltd.simprosuite.com/api/v1.0/companies/0/sites/37304
                            $siteDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID);
                            dump("/api/v1.0/companies/0/sites/" . $site_contract->ID);
                            dump($siteDetails);
                            $siteAssetsDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID . '/assets/' . $site_contract->ID);
                            dump($siteAssetsDetails);
                            $value = "DEFAULT TEXT";
                            if (isset($siteAssetsDetails->CustomFields) && is_array($siteAssetsDetails->CustomFields)) {
                                foreach ($siteAssetsDetails->CustomFields as $cf) {
                                    if ($cf->CustomField->ID == 1043) {
                                        $value = $cf->CustomField->Value;
                                    }
                                }
                            }

                            $company_information = [
                                'id' => $company->ID,
                                'company_name' => $company->CompanyName,
                                'Address' => $company->Address->Address,
                                'City' => $company->Address->City,
                                'State' => $company->Address->State,
                                'PostalCode' => $company->Address->PostalCode,
                                'Country' => $company->Address->Country,
                                'contract' => [
                                    'id' => $contractNeeded->ID,
                                    'name' => $contractNeeded->Name,
                                    'endDate' => $contractNeeded->EndDate,
                                    'value' => $contractNeeded->Value
                                ],
                                'sites' => [
                                    'id' => $siteDetails->ID,
                                    'Address' => $siteDetails->Address->Address,
                                    'City' => $siteDetails->Address->City,
                                    'State' => $siteDetails->Address->State,
                                    'PostalCode' => $siteDetails->Address->PostalCode,
                                ],
                                'asset' => [
                                    'id' => $site_contract->ID,
                                    'value' => $value,
                                ]
                            ];
                            dump($company_information);
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