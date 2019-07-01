<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/28/18
 * Time: 12:53 PM
 */

namespace App\Service;


use App\Appointment;
use App\Models\Asset;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\ProcessedPrivate;
use App\Models\Site;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;

class PrivateUploader
{
    private $requester;

    public function __construct()
    {
        $this->requester = new Requester();
    }

    public function start()
    {
        $promise = $this->requester->getRequestAsync('companies/0/customers/companies/', [
            'pageSize' => 1,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $headers = $res->getHeaders();
                if (array_key_exists('Result-Total', $headers)) {
                    $pages = (int)ceil($headers['Result-Total'][0] / 25);
                    for ($i = $pages; $i > 0; $i--) {
                        dump('parse page ' . $i);
                        $this->parsePage($i);
                    }
                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
    }

    public function parsePage($page_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/customers/companies/', [
            'pageSize' => 25,
            'page' => $page_id,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $result = json_decode($res->getBody()->getContents());
                foreach ($result as $company_info) {
                    dump('addParseCompanies');
                    $this->addParseCompanies($company_info);
                }
            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addParseCompanies($company_info)
    {
        $promise = $this->requester->getRequestAsync('companies/0/customers/companies/' . $company_info->ID, []);
        $promise->then(
            function (ResponseInterface $res) {
                $company = json_decode($res->getBody()->getContents());
                dump('addParseContracts');
                $this->addParseContracts($company);
            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addParseContracts($company)
    {
        $promise = $this->requester->getRequestAsync('companies/0/customers/' . $company->ID . '/contracts/', []);
        $promise->then(
            function (ResponseInterface $res) use ($company) {
                $contracts = json_decode($res->getBody()->getContents());
                dump(sizeof($contracts));
                if (sizeof($contracts) > 0) {
                    dump('addCreateCompanyAndParseContracts');
                    $this->addCreateCompanyAndParseContracts($company, $contracts);
                }
            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addCreateCompanyAndParseContracts($company, $contracts)
    {
        $customer = Customer::create([
            'company_name' => $company->CompanyName ?? null,
            'first_name' => $company->GivenName ?? null,
            'last_name' => $company->FamilyName ?? null,
            'company_id' => $company->ID ?? null,
            'address' => $company->Address->Address ?? null,
            'city' => $company->Address->City ?? null,
            'state' => $company->Address->State ?? null,
            'country' => $company->Address->Country ?? null,
            'postal_code' => $company->Address->PostalCode ?? null,
        ]);

        dump($customer);

        foreach ($contracts as $contract_info) {
            dump('addParseContract');
            $this->addParseContract($company, $contract_info);
        }
    }

    public function addParseContract($company, $contract_info)
    {
        $promise = $this->requester->getRequestAsync('companies/0/customers/' . $company->ID . '/contracts/' . $contract_info->ID, []);
        $promise->then(
            function (ResponseInterface $res) use ($company) {
                $company_instance = Customer::where('company_id', '=', $company->ID)->first();
                $contract_info = json_decode($res->getBody()->getContents());
                if ($company_instance) {
                    $contract = Contract::where('contract_id', '=', $contract_info->ID)->first();
                    if ($contract) {
                        $contract->name = $contract_info->Name ?: $contract->name;
                        $contract->end_date = $contract_info->EndDate ? \DateTime::createFromFormat('Y-m-d', $contract_info->EndDate) : $contract->end_date;
                        $contract->value = $contract_info->Value ?: $contract->value;
                        $contract->save();
                    } else {
                        $contract = $company_instance->contracts()->create([
                            'contract_id' => $contract_info->ID,
                            'name' => $contract_info->Name ?? null,
                            'end_date' => $contract_info->EndDate ? \DateTime::createFromFormat('Y-m-d', $contract_info->EndDate) : null,
                            'value' => $contract_info->Value ?? null,
                        ]);
                    }

                    dump($contract);


                    if (isset($company->Sites)) {
                        foreach ($company->Sites as $site_id) {
                            dump('addParseSite');
                            $this->addParseSite($company, $contract->contract_id, $site_id->ID);
                        }
                    }
                }

            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addParseSite($company, $contract_id, $site_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $site_id, []);
        $promise->then(
            function (ResponseInterface $res) use ($company, $contract_id, $site_id) {
                $site_info = json_decode($res->getBody()->getContents());
                $company_instance = Customer::where('company_id', '=', $company->ID)->first();
                if ($company_instance) {
                    $site = Site::where('site_id', '=', $site_info->ID)->first();
                    if ($site) {
                        $site->city = $site_info->Address->City ?? $site->city;
                        $site->address = $site_info->Address->Address ?? $site->address;
                        $site->state = $site_info->Address->State ?? $site->state;
                        $site->postal_code = $site_info->Address->PostalCode ?? $site->postal_code;
                        $site->save();
                    } else {
                        $site = $company_instance->sites()->create([
                            'site_id' => $site_info->ID,
                            'city' => $site_info->Address->City ?? null,
                            'address' => $site_info->Address->Address ?? null,
                            'state' => $site_info->Address->State ?? null,
                            'postal_code' => $site_info->Address->PostalCode ?? null,
                        ]);
                    }
                    dump($site);
                    dump('addParseSitesContract');
                    $this->addParseSitesContract($site_id, $contract_id);
                }

            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addParseSitesContract($site_id, $contract_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $site_id . '/assets/?CustomerContract ne()', []);
        $promise->then(
            function (ResponseInterface $res) use ($site_id, $contract_id) {
                $sites_contracts = json_decode($res->getBody()->getContents());
                foreach ($sites_contracts as $site_contract) {
                    if (!isset($site_contract->CustomerContract->ID) ||
                        $site_contract->CustomerContract->ID != $contract_id
                    ) {
                        continue;
                    }
                    dump('addSiteContractAsset');
                    $this->addSiteContractAsset($site_id, $contract_id, $site_contract->ID);
                }

            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();
    }

    public function addSiteContractAsset($site_id, $contract_id, $asset_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $site_id . '/assets/' . $asset_id, []);
        $promise->then(
            function (ResponseInterface $res) use ($site_id, $contract_id) {
                $asset_details = json_decode($res->getBody()->getContents());
                $value = "DEFAULT TEXT";
                if (isset($asset_details->CustomFields) && is_array($asset_details->CustomFields)) {
                    foreach ($asset_details->CustomFields as $cf) {
                        if ($cf->CustomField->ID == 1043) {
                            $value = $cf->CustomField->Value;
                        }
                    }
                }

                $site = Site::where('site_id', '=', $site_id)->first();
                $contract = Contract::where('contract_id', '=', $contract_id)->first();

                if (!$site || !$contract) {
                    return;
                }
                $asset = Asset::create([
                    'asset_id' => $asset_details->ID,
                    'value' => $value,
                ]);
                $site->assets()->save($asset);
                $contract->assets()->save($asset);
                $site->save();
                $contract->save();
            },
            function (RequestException $e) {
                echo ' error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $promise->wait();


    }


}