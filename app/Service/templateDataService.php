<?php
namespace App\Service;

use App\Customers;
use App\SimProContracts;
use App\SimProJobs;

class templateDataService
{
    private $data=[
                'TodayDate'=>'',
                'CompanyName'=>'',
                'CompanyAddress1'=>'',
                'CompanyAddress2'=>'',
                'CompanyCity'=>'',
                'CompanyCounty'=>'',
                'CompanyPostCode'=>'',
                'CustomerID'=>'',
                'FirstName'=>'',
                'LastName'=>'',
                'Address'=>'',
                'Address2'=>'',
                'AddressCity'=>'',
                'AddressCounty'=>'',
                'ContactFirst'=>'',
                'ContactLast'=>'',
                'PropertyAddress'=>'',
                'PropertyAddress2'=>'',
                'PropertyCity'=>'',
                'PropertyCounty'=>'',
                'PropertyPostCode'=>'',
                'SiteFirstName'=>'',
                'SiteLastName'=>'',
                'SiteAddress'=>'',
                'SiteAddress2'=>'',
                'SiteCity'=>'',
                'SiteCounty'=>'',
                'SitePostcode'=>'',
                'SFirstName'=>'',
                'SLastName'=>'',
                'ContactPhone'=>'',
                'JobNumber'=>'',
                'DueDate'=>'',
                'HousingCompany'=>'',
                'ServiceType'=>'',
                'Assetname'=>''
                ];
    public function makeTemplateDataPrivate(SimProContracts $contract){
        $sps=new simProService();

        $customer=Customers::find($contract->customers_id);
        $customerParsedData=json_decode($customer->parsedData);
        $contractParsedData=json_decode($contract->parsedData);

        $siteData=$sps->getSiteData($customer->company_id,23967);
        #todo надо забрать из списка сайтов
        #TODO вероятно там более сложная логика, нужно будет смотреть на кастом поля в сайте и искать там контракт
        dd($siteData);
        $contactData=$sps->getContactData($customer->company_id,$customerParsedData->ID);
        dd($contactData);


        $data=$this->data;
        $data['TodayDate']=date('d M Y',time());

        //CUSTOMERS
        if(strpos($customer->apiurl,'s/companies')){
            #companies
            $data['CompanyName']=$customerParsedData->CompanyName;
            $data['CompanyAddress1']=$customerParsedData->Address->Address;
            $data['CompanyAddress2']='';
            $data['CompanyCity']=$customerParsedData->Address->City;
            $data['CompanyCounty']=$customerParsedData->Address->Country;
            $data['CompanyPostCode']=$customerParsedData->Address->PostalCode;
            $data['CustomerID']=$customerParsedData->ID;
        }else{
            #individuals
            $data['FirstName']=$customerParsedData->GivenName;
            $data['LastName']=$customerParsedData->FamilyName;
            $data['Address']=$customerParsedData->Address->Address;
            $data['Address2']='';
            $data['AddressCity']=$customerParsedData->Address->City;
            $data['AddressCounty']=$customerParsedData->Address->Country;
            $data['CompanyPostCode']=$customerParsedData->Address->PostalCode;
            $data['CustomerID']=$customerParsedData->ID;
        }
        dd($contractParsedData);
        //CONTRACTS
        $data['TotalDue']=$contractParsedData->Value;
        $data['ExpiryDate']=date("d/m/Y",strtotime($contractParsedData->EndDate));
        $data['PlanType']=$contractParsedData->Name;

        if($siteData) {
            //SITES
            $data['PropertyAddress'] = $siteData->Address->Address;
            $data['PropertyAddress2'] = '';
            $data['PropertyCity'] = $siteData->Address->City;
            $data['PropertyCounty'] = $siteData->Address->Country;
            $data['PropertyPostCode'] = $siteData->Address->PostalCode;

            //SITES
            $daa['SiteFirstName'] = $siteData->PrimaryContact->GivenName;
            $data['SiteLastName'] = $siteData->PrimaryContact->FamilyName;
            $data['SiteAddress'] = $siteData->Address->Address;
            $data['SiteAddress2'] = '';
            $data['SiteCity'] = $siteData->Address->City;
            $data['SiteCounty'] = $siteData->Address->Country;
            $data['SitePostcode'] = $siteData->Address->PostalCode;
            $data['SFirstName'] = $siteData->PrimaryContact->GivenName;
            $data['SLastName'] = $siteData->PrimaryContact->FamilyName;
            $data['ContactPhone'] = $siteData->PrimaryContact->WorkPhone;
        }
        //CONTACTS
        if(is_array($contactData)&&count($contactData)>0){
            $contactData=$contactData[0];
            $data['ContactFirst']=$contactData->GivenName;
            $data['ContactLast']=$contactData->FamilyName;
        }

        $data['ServiceType']='ERR';
        $data['Assetname']='ERR';

        return $data;
    }
    public function makeTemplateDataHousing(SimProJobs $job){
        $sps=new simProService();
        $customer=Customers::where('simpro_id','=',$job->simpro_customer_id)->first();
        $customerParsedData=json_decode($customer->parsedData);
        $jobParsedData=json_decode($job->parsedData);

        $contactData=$sps->getContactData($customer->company_id,$customerParsedData->ID);
        $siteData=$sps->getSiteData($customer->company_id,$jobParsedData->Site->ID);

        $data=$this->data;

        $data['TodayDate']=date('d M Y',time());
        //CUSTOMERS
        if(strpos($customer->apiurl,'s/companies')){
            #companies
            $data['CompanyName']=$customerParsedData->CompanyName;
            $data['CompanyAddress1']=$customerParsedData->Address->Address;
            $data['CompanyAddress2']='';
            $data['CompanyCity']=$customerParsedData->Address->City;
            $data['CompanyCounty']=$customerParsedData->Address->Country;
            $data['CompanyPostCode']=$customerParsedData->Address->PostalCode;
            $data['CustomerID']=$customerParsedData->ID;
        }else{
            #individuals
            $data['FirstName']=$customerParsedData->GivenName;
            $data['LastName']=$customerParsedData->FamilyName;
            $data['Address']=$customerParsedData->Address->Address;
            $data['Address2']='';
            $data['AddressCity']=$customerParsedData->Address->City;
            $data['AddressCounty']=$customerParsedData->Address->Country;
            $data['CompanyPostCode']=$customerParsedData->Address->PostalCode;
            $data['CustomerID']=$customerParsedData->ID;
        }


        //CONTACTS
        if(is_array($contactData)&&count($contactData)>0){
            $contactData=$contactData[0];
            $data['ContactFirst']=$contactData->GivenName;
            $data['ContactLast']=$contactData->FamilyName;
        }


        if($siteData) {
            //SITES
            $data['PropertyAddress']=$siteData->Address->Address;
            $data['PropertyAddress2']='';
            $data['PropertyCity']=$siteData->Address->City;
            $data['PropertyCounty']=$siteData->Address->Country;
            $data['PropertyPostCode']=$siteData->Address->PostalCode;
            //SITES
            $daa['SiteFirstName'] = $siteData->PrimaryContact->GivenName;
            $data['SiteLastName'] = $siteData->PrimaryContact->FamilyName;
            $data['SiteAddress'] = $siteData->Address->Address;
            $data['SiteAddress2'] = '';
            $data['SiteCity'] = $siteData->Address->City;
            $data['SiteCounty'] = $siteData->Address->Country;
            $data['SitePostcode'] = $siteData->Address->PostalCode;
            $data['SFirstName'] = $siteData->PrimaryContact->GivenName;
            $data['SLastName'] = $siteData->PrimaryContact->FamilyName;
            $data['ContactPhone'] = $siteData->PrimaryContact->WorkPhone;
        }
        //JOBS
        $data['JobNumber']=$jobParsedData->ID;
        $data['DueDate']=$jobParsedData->DueDate;
        $data['HousingCompany']=$jobParsedData->Customer->CompanyName;
        return $data;
    }
}