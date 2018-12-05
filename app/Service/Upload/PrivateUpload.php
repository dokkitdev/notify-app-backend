<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 2:39 PM
 */

namespace App\Service\Upload;


use App\Service\simProRequestService;

class PrivateUpload
{
    /** @var simProRequestService */
    private $simpro;

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }


    public function run()
    {
        $pages = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/customers/companies/');
        if ($pages) {
            foreach ($pages as $url) {
                $this->parsePageByUrl($url);
            }
        }
    }

    public function parsePageByUrl($url)
    {
        $companies = $this->simpro->getRequest('get', $url);
        foreach ($companies as $company_info) {
            $this->parseCompany($company_info);
            die;
        }
    }

    public function parseCompany($company_info)
    {
        $company = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/' . $company_info->ID);
        $this->parseContract($company);
    }

    public function parseContract($company)
    {
        $contracts = $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/' . $company->ID . '/contracts/');
        if (sizeof($contracts) > 0) {
            $this->addCreateCompanyAndParseContracts($company, $contracts);
        }
    }

    public function addCreateCompanyAndParseContracts()
    {

    }

}