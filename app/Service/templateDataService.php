<?php
namespace App\Service;

use App\Customers;
use App\SimProContracts;
use App\SimProJobs;

class templateDataService
{
    public function makeTemplateDataPrivate(SimProContracts $contract){
        $sps=new simProService();

        $customer=Customers::find($contract->customers_id);
        $customerParsedData=json_decode($customer->parsedData);
        $contractParsedData=json_decode($contract->parsedData);
        dd($contractParsedData);
        $siteData=$sps->getSiteData($customer->company_id,23967);
        #todo надо забрать из списка сайтов
        #TODO вероятно там более сложная логика, нужно будет смотреть на кастом поля в сайте и искать там контракт

        $data=[];
        $data['customer_address']=$customer->address;
        $data['date_now']=date('d M Y',time());
        if (property_exists($customerParsedData, 'Title') && property_exists($customerParsedData, 'FamilyName')) {
            $data['name'] = $customerParsedData->Title . ' ' . $customerParsedData->FamilyName;
        }
        //$data['site_address']=$siteData->Address->Address;
        $data['site_assets']='ERROR';
        $data['contract_tab']='ERROR';
        $data['from_name']='ERROR';
        $data['client_address']='ERROR';
        if (property_exists($customerParsedData, 'ContractNo'))
            $data['contract_ref']=$contractParsedData->ContractNo;
        if (property_exists($customerParsedData, 'ID'))
            $data['customer_ref']=$customerParsedData->ID;
        if (property_exists($customerParsedData, 'EndDate'))
            $data['date_expired']=date("d/m/Y",strtotime($contractParsedData->EndDate));
        return $data;
    }
    public function makeTemplateDataHousing(SimProJobs $job){
        $sps=new simProService();
        $customer=Customers::where('simpro_id','=',$job->simpro_customer_id)->first();
        $customerParsedData=json_decode($customer->parsedData);
        $jobParsedData=json_decode($job->parsedData);
        $siteData=$sps->getSiteData($customer->company_id,$jobParsedData->Site->ID);
        $data=[];
        $data['customer_address']=$customer->address;
        $data['site_address']=$siteData->Address->Address;
        $data['site_primary_contact']=$siteData->PrimaryContact->Title.' '.$siteData->PrimaryContact->FamilyName;
        $data['job_number']=$jobParsedData->ID;
        $data['site_name']=$siteData->Name;
        $data['date_now']=date('d M Y',time());
        return $data;
    }
    /*

    COMPANY CUSTOMERS
/api/v1.0/companies/{companyID}/customers/companies/{customerID}
{{CompanyName}} “CompanyName”
{{CompanyAddress1}} “Address”
{{CompanyAddress2}}
{{CompanyCity}} “City”
{{CompanyCounty}} “State”
{{CompanyPostCode}} “PostalCode”
{{CustomerID}} “ID”
INDIVIDUAL CUSTOMERS
/api/v1.0/companies/{companyID}/customers/individuals/{customerID}
{{FirstName}} “GivenName”
{{LastName}} “FamilyName”
{{Address}} “Address”
{{Address2}}
{{AddressCity}} “City”
{{AddressCounty}} “State”
{{CompanyPostCode}} “PostalCode”
{{CustomerID}} “ID”
CONTRACTS
/api/v1.0/companies/{companyID}/customers/{customerID}/contracts/{contractID}
{{TotalDue}} “Value”
{{ExpiryDate}} “EndDate”
SITES
/api/v1.0/companies/{companyID}/sites/{siteID}
{{PropertyAddress}} “Address”
{{PropertyAddress2}}
{{PropertyCity}} “City”
{{PropertyCounty}} “State”
{{PropertyPostCode}} “PostalCode”
{{AssetName}} “Name”
{{PlanType}}

     */
}