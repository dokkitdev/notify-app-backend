<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 2:39 PM
 */

namespace App\Service\Upload;


use App\Models\Asset;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Site;
use App\Service\simProRequestService;
use Illuminate\Support\Facades\DB;

class PrivateUpload
{
    /** @var simProRequestService */
    private $simpro;
    private $customer;
    private $contract;
    private $site;

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }

    public function runCustomers()
    {
        DB::delete('TRUNCATE `n_customers`;');
        $pages = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/customers/companies/');
        if ($pages) {
            foreach ($pages as $url) {
                dump('parse page by url');
                $this->parsePageByUrl($url);
            }
        }
        $pages = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/customers/individuals/');
        if ($pages) {
            foreach ($pages as $url) {
                dump('parsePageByUrlIndividual');
                $this->parsePageByUrlIndividual($url);
            }
        }
    }

    public function parsePageByUrl($url)
    {
        $companies = $this->simpro->getRequest('get', $url);
        foreach ($companies as $company_info) {
            $this->parseCompany($company_info);
        }
    }

    public function parsePageByUrlIndividual($url)
    {
        dump($url);
        $companies = $this->simpro->getRequest('get', $url);
        foreach ($companies as $company_info) {
            dump('parseIndividual   ');
            $this->parseIndividual($company_info);
        }
    }


    public function parseIndividual($company_info)
    {
        $company = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/individuals/' . $company_info->ID);
        dump('parse contract');
        $this->parseContract($company, false);
    }

    public function parseCompany($company_info)
    {
        $company = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/' . $company_info->ID);
        $this->parseContract($company, true);
    }

    public function parseContract($company, $is_company)
    {
        $contracts = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/' . $company->ID . '/contracts/');
        if (sizeof($contracts) > 0) {
            dump('addCreateCompanyAndParseContracts');
            $this->addCreateCompanyAndParseContracts($company, $contracts,$is_company);
        } else {
            dump('zero');
        }
    }

    public function addCreateCompanyAndParseContracts($company, $contracts, $is_company)
    {
        $this->customer = Customer::create([
            'company_name' => $company->CompanyName ?? null,
            'first_name' => $company->GivenName ?? null,
            'title' => $company->Title ?? null,
            'last_name' => $company->FamilyName ?? null,
            'company_id' => $company->ID ?? null,
            'address' => $company->Address->Address ?? null,
            'city' => $company->Address->City ?? null,
            'state' => $company->Address->State ?? null,
            'country' => $company->Address->Country ?? null,
            'postal_code' => $company->Address->PostalCode ?? null,
            'email' => $company->Email ?? null,
            'is_company' => $is_company
        ]);
        dump('create customer');
    }



    public function run()
    {
        DB::delete('TRUNCATE `n_assets`;');
        DB::delete('TRUNCATE `n_contracts`;');
        DB::delete('TRUNCATE `n_sites`;');

        $customers = Customer::all();
//        dump($customers);
        foreach ($customers as $customer) {
            $this->customer = $customer;
            if ($customer->is_company) {
                $company = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/' . $customer->company_id);
                if ($company) {
                    $contracts = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/' . $company->ID . '/contracts/');
                    if (sizeof($contracts) > 0) {
                        foreach ($contracts as $contract) {
                            $this->addParseContract($company, $contract);
                        }
                    }
                }
            } else {
                $company = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/individuals/' . $customer->company_id);
                if ($company) {
                    $contracts = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/' . $company->ID . '/contracts/');
                    if (sizeof($contracts) > 0) {
                        foreach ($contracts as $contract) {
                            $this->addParseContract($company, $contract);
                        }
                    }
                }
            }
        }
    }





    public function addParseContract($company, $contract_info)
    {

        dump('/api/v1.0/companies/0/customers/' . $company->ID . '/contracts/' . $contract_info->ID);
        dump($contract_info);
        $contract_info = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/' . $company->ID . '/contracts/' . $contract_info->ID);

        if ($contract_info) {
            $contract = Contract::where('contract_id', '=', $contract_info->ID)->first();
            if ($contract) {
                $contract->name = $contract_info->Name ?: $contract->name;
                $contract->end_date = $contract_info->EndDate ? \DateTime::createFromFormat('Y-m-d', $contract_info->EndDate) : $contract->end_date;
                $contract->value = $contract_info->Value ?: $contract->value;
                $contract->save();
            } else {
                $contract = $this->customer->contracts()->create([
                    'contract_id' => $contract_info->ID,
                    'name' => $contract_info->Name ?? null,
                    'contract_no' => $contract_info->ContractNo ?? null,
                    'end_date' => $contract_info->EndDate ? \DateTime::createFromFormat('Y-m-d', $contract_info->EndDate) : null,
                    'value' => $contract_info->Value ?? null,
                ]);
            }

            if (isset($company->Sites) && sizeOf($company->Sites) > 0) {
                foreach ($company->Sites as $site_id) {
                    dump('addParseSite');
                    $this->addParseSite($company, $contract, $site_id->ID);
                }
            }
        } else {
        }
    }

    public function addParseSite($company, $contract, $site_id)
    {
        $site_info = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site_id);

        if ($company) {
            $site = Site::where('site_id', '=', $site_info->ID)->first();
            if ($site) {
                $site->city = $site_info->Address->City ?? $site->city;
                $site->address = $site_info->Address->Address ?? $site->address;
                $site->state = $site_info->Address->State ?? $site->state;
                $site->postal_code = $site_info->Address->PostalCode ?? $site->postal_code;
                $site->save();
            } else {
                $site = $this->customer->sites()->create([
                    'site_id' => $site_info->ID,
                    'city' => $site_info->Address->City ?? null,
                    'address' => $site_info->Address->Address ?? null,
                    'state' => $site_info->Address->State ?? null,
                    'postal_code' => $site_info->Address->PostalCode ?? null,
                ]);
            }
            dump('addParseSitesContract');
            $this->addParseSitesContract($site, $contract);
        }

    }

    public function addParseSitesContract($site, $contract)
    {
        $sites_contracts = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site->site_id . '/assets/?CustomerContract ne()');

        foreach ($sites_contracts as $site_contract) {
            if (!isset($site_contract->CustomerContract->ID) ||
                $site_contract->CustomerContract->ID != $contract->contract_id
            ) {
                continue;
            }
            dump('addSiteContractAsset');
            $this->addSiteContractAsset($site, $contract, $site_contract->ID);
        }
    }

    public function addSiteContractAsset($site, $contract, $asset_id)
    {
        $asset_details = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site->site_id . '/assets/' . $asset_id);
        $value = "Heating Equipment";
        $value1044 = '';
        if (isset($asset_details->CustomFields) && is_array($asset_details->CustomFields)) {
            foreach ($asset_details->CustomFields as $cf) {
                if ($cf->CustomField->ID == 1043) {
                    $value = $cf->Value;
                }
                if ($cf->CustomField->ID == 1044) {
                    $value1044 = $cf->Value;
                }
            }
        }

        $joined = $value == 'Heating Equipment'
            ? $value
            : trim($value . ' ' . $value1044);

        $asset = Asset::create([
            'asset_id' => $asset_details->ID,
            'value' => $joined,
        ]);
        $site->assets()->save($asset);
        $contract->assets()->save($asset);
        $site->save();
        $contract->save();


    }

}