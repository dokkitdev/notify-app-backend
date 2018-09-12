<?php
/**
 * Created by PhpStorm.
 * User: OmenWD
 * Date: 04/09/18
 * Time: 14:53
 */

namespace App\Service;


use App\Customers;
use App\Jobs\parseCustomers;
use App\Jobs\parseCustomersLinks;
use App\Jobs\parseJobs;
use App\Jobs\parseJobsLinks;

class simProService
{
    private $simProRequest;
    public function __construct()
    {
        $this->simProRequest=new simProRequestService();
    }
    public function updateSimProCustomer($data,Customers $customer){
        $res=$this->simProRequest->patchRequest('PATCH',$customer->apiurl,$data);
    }

    public function importCustomers(){
        $companies=$this->parseCompanies();
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        $this->parseCustomersCompaniesPg($companies);
        exit;
    }
    public function importJobs(){
        $companies=$this->parseCompanies();
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        $jobsLinksPg=$this->parseJobsCompaniesPg($companies);
        /*foreach ($jobsLinksPg as $item) {

            $jobsLinks[]=$this->parseJobsLinks($item['url'],$item['companyId']);

        }
        dd($jobsLinks);*/
        exit;
    }
    /**
     * собирает ссылки на постраничку для запроса списка job определенныйх компаний
     *
     */
    function parseJobsCompaniesPg($companies){
        $jobsLinks=[];
        foreach ($companies as $v){
            if($v->ID==0)continue;#todo оставить (у 0 компании выдает 500 ошибку)
            $jobs=$this->simProRequest->getRequestPage('GET','/api/v1.0/companies/'.$v->ID.'/jobs/');
            if(count($jobs)>0){
                foreach ($jobs as $val){
                    $jobsLinks[]=['url'=>$val,'companyId'=>$v->ID];
                    parseJobsLinks::dispatch($val,$v->ID)->delay(now()->addSecond(5));
                }
            }
        }
        return $jobsLinks;
    }

    /**
     * собирает ссылки на определенный job для парсинга
     * @param $link - ссылка с постраничкой или без для хапроа списка job
     * @param $companyId - id компании нужно для создания сылки
     * @return array
     */
    public function parseJobsLinks($link,$companyId){
        $jobs=$this->simProRequest->getRequest('GET',$link);
        $JobsLinks=[];
        if(count($jobs)>0){
            foreach ($jobs as $val){
                $JobsLinks[]='/api/v1.0/companies/'.$companyId.'/jobs/'.$val->ID;
                parseJobs::dispatch('/api/v1.0/companies/'.$companyId.'/jobs/'.$val->ID,$companyId,$val->ID)->delay(now()->addSecond(5));
            }
        }
        return $JobsLinks;
    }


    function parseCustomersCompaniesPg($companies){
        $customersLinks=[];
        foreach ($companies as $v){
            $customers=$this->simProRequest->getRequestPage('GET','/api/v1.0/companies/'.$v->ID.'/customers/');
            if(count($customers)>0){
                foreach ($customers as $val){
                    //$customersLinks[]=$val;
                    parseCustomersLinks::dispatch($val)->delay(now()->addSecond(5));
                }
            }
        }
        return $customersLinks;
    }



    public function parseCustomerLinks($link){
        $customers=$this->simProRequest->getRequest('GET',$link);
        if(count($customers)>0){
            foreach ($customers as $val){
//                $customersLinks[]=$val->_href;
                parseCustomers::dispatch($val->_href)->delay(now()->addSecond(5));
            }
        }

        //       return $customersLinks;
    }





    function parseCustomers($customers=[]){
        foreach($customers as $v) {
            parseCustomers::dispatch($v)->delay(now()->addSecond(5));
        }
        return true;
    }
    function parseCustomerByUrl($url){
        $customer=$this->simProRequest->getRequest('GET',$url);
        return $customer;
    }
    function parseJobByUrl($url){
        $job=$this->simProRequest->getRequest('GET',$url);
        return $job;
    }
    function parseCustomerContractByUrl($url){
        $parsedContracts=false;
        $contracts=$this->simProRequest->getRequest('GET',$url); # забираем краткую информацию по контрактам
        if(count($contracts)>0){
            foreach ($contracts as $contract) { # забираем подробную информацию по контрактам
                $parsedContracts[]=$this->simProRequest->getRequest('GET',$url.$contract->ID);
            }

        }

        $res=false;
        if(is_array($parsedContracts)&&count($parsedContracts)>1){ #если несколько контрактов ищем первый НЕ Архивный
            foreach($parsedContracts as $v){
                if($v->Archived==false){
                    $res=$v;
                    break;
                }
            }
            return $res;
        }else {
            return isset($parsedContracts[0])?$parsedContracts[0]:false;
        }
    }
    private function parseCompanies(){
        $companies=$this->simProRequest->getRequest('GET','/api/v1.0/companies/');
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        return $companies;
    }

    private function parseCustomersLinks($companies){
        $customersLinks=[];
        foreach ($companies as $v){
            $customers=$this->simProRequest->getRequest('GET','/api/v1.0/companies/'.$v->ID.'/customers/');
            if(count($customers)>0){
                foreach ($customers as $val){
                    $customersLinks[]=$val->_href;
                }
            }
        }
        return $customersLinks;
    }
}