<?php

namespace App\Jobs;

use App\Customers;
use App\Service\collectDataService;
use App\Service\simProService;
use App\SimProContracts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class parseCustomers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $url;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url=$url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->attempts()>5){ # удаление после 3-х не удачных попыток
            $this->delete();
        }
        preg_match('!\D(\/companies\/|\/individuals\/)(.*+)$!',$this->url,$matches); # get the customer id
        preg_match('!\/companies\/(.*)\/customers\/!',$this->url,$matchesC); # get the companie id
        $customer=$this->getCustomer(isset($matches[2])?$matches[2]:false);
        $simProservice=new simProService();
        $parsedCustomer=$simProservice->parseCustomerByUrl($this->url);
        if(!$parsedCustomer)$this->delete();

        $customer->parsedData=json_encode($parsedCustomer);



        /**
         * согласно ТЗ оринтируемся на наличие тега Housing у кастомера
         */
        $housing=false;
        if(isset($parsedCustomer->Tags)&&count($parsedCustomer->Tags)>0){
            foreach($parsedCustomer->Tags as $v){
                if($v->Name=='Housing'){
                    $housing=true;
                    break;
                }
            }

        }
        if($housing)$customer->customer_group_tag='Housing';
        else $customer->customer_group_tag='Private';


        $customer->company_name=isset($parsedCustomer->CompanyName)?$parsedCustomer->CompanyName:'';
        $customer->company_id=$matchesC[1];
        $customer->given_name=isset($parsedCustomer->GivenName)?$parsedCustomer->GivenName:'';
        $customer->family_name=isset($parsedCustomer->FamilyName)?$parsedCustomer->FamilyName:'';
        $customer->simpro_id=$parsedCustomer->ID;
        $customer->address=isset($parsedCustomer->Address->Address)?$parsedCustomer->Address->Address:'';
        $customer->city=isset($parsedCustomer->Address->City)?$parsedCustomer->Address->City:'';
        $customer->state=isset($parsedCustomer->Address->State)?$parsedCustomer->Address->State:'';
        $customer->postal_code=isset($parsedCustomer->Address->PostalCode)?$parsedCustomer->Address->PostalCode:'';
        $customer->country=isset($parsedCustomer->Address->Country)?$parsedCustomer->Address->Country:'';
        $customer->customer_type=isset($parsedCustomer->CustomerType)?$parsedCustomer->CustomerType:'';
        $customer->email=isset($parsedCustomer->Email)?$parsedCustomer->Email:'';

        if($customer->customer_type=='Lead'){ # don't process the Lead, only Customer
            $this->delete();
            return true;
        }

        $customer->apiurl=$this->url;
        //$customer->customer_group=isset($parsedCustomer->Profile->CustomerGroup)?$parsedCustomer->Profile->CustomerGroup:'';
        $customer->save();

        #todo надо забирать ВСЕ контракты и инсертить(апдэйтить) их в таблицу контрактов в связке с текущим кастомером
        $parsedCustomerContracts=$simProservice->parseCustomerContractByUrl('/api/v1.0/companies/'.$matchesC[1].'/customers/'.$matches[2].'/contracts/');
        if($parsedCustomerContracts) { #сохраняем контракты Customer-а
            foreach ($parsedCustomerContracts as $parsedCustomerContract) {
                $customerContract=$this->getContract($parsedCustomerContract->ID);
                $customerContract->simpro_id = $parsedCustomerContract->ID;
                $customerContract->customers_id = $customer->id;
                $customerContract->start_date = isset($parsedCustomerContract->StartDate) ? strtotime($parsedCustomerContract->StartDate) : '';
                $customerContract->end_date = isset($parsedCustomerContract->EndDate) ? strtotime($parsedCustomerContract->EndDate) : '';
                $customerContract->contract_no = isset($parsedCustomerContract->ContractNo) ? $parsedCustomerContract->ContractNo : '';
                $customerContract->contract_name = isset($parsedCustomerContract->Name) ? $parsedCustomerContract->Name : '';
                $customerContract->parsedData = json_encode($parsedCustomerContract);
                $customerContract->save();

                $process=new collectDataService();
                $process->processContract($customerContract->id);
            }
        }

    }
    private function getCustomer($simpro_id=false){
        if($simpro_id)$customer=Customers::where(['simpro_id'=>$simpro_id])->get()->toArray();
        if($simpro_id&&isset($customer[0]['id']))return Customers::find($customer[0]['id']);
        else return new Customers();
    }
    private function getContract($simpro_id=false){
        if($simpro_id)$contract=SimProContracts::where(['simpro_id'=>$simpro_id])->get()->toArray();
        if($simpro_id&&isset($contract[0]['id']))return SimProContracts::find($contract[0]['id']);
        else return new SimProContracts();
    }
}
