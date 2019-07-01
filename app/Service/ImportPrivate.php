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
            /** Получаем компанию */
            $company = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/companies/' . $c->ID);
            $company_information = [
                'id' => $company->ID,
                'company_name' => $company->CompanyName,
                'Address' => $company->Address->Address,
                'City' => $company->Address->City,
                'State' => $company->Address->State,
                'PostalCode' => $company->Address->PostalCode,
                'Country' => $company->Address->Country,
            ];

            $contracts_id = [];
            foreach ($contracts as $contId) {
                $contractNeeded = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/customers/' . $c->ID . '/contracts/' . $contId->ID);
                $contract = [
                    'id' => $contractNeeded->ID,
                    'name' => $contractNeeded->Name,
                    'endDate' => $contractNeeded->EndDate,
                    'value' => $contractNeeded->Value
                ];
                $contracts_id[] = $contractNeeded->ID;
            }

            foreach ($company->Sites as $sId) {
                $sites_contracts = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID . '/assets/?CustomerContract ne()');

                $siteDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID);
                $site = [
                    'id' => $siteDetails->ID,
                    'Address' => $siteDetails->Address->Address,
                    'City' => $siteDetails->Address->City,
                    'State' => $siteDetails->Address->State,
                    'PostalCode' => $siteDetails->Address->PostalCode,
                ];

                foreach ($sites_contracts as $site_contract) {
                    if (!isset($site_contract->CustomerContract->ID) ||
                        !in_array($site_contract->CustomerContract->ID, $contracts_id)
                    ) {
                        continue;
                    }

                    $siteAssetsDetails = $this->simProRequest->getRequest('GET', '/api/v1.0/companies/0/sites/' . $sId->ID . '/assets/' . $site_contract->ID);
                    $value = "DEFAULT TEXT";
                    if (isset($siteAssetsDetails->CustomFields) && is_array($siteAssetsDetails->CustomFields)) {
                        foreach ($siteAssetsDetails->CustomFields as $cf) {
                            if ($cf->CustomField->ID == 1043) {
                                $value = $cf->CustomField->Value;
                            }
                        }
                    }

                    $site_contract_asset = [
                        'contract_id' => $site_contract->CustomerContract->ID,
                        'site_id' => $sId->ID,
                        'asset_id' => $site_contract->ID,
                        'value' => $value,
                    ];
                }
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