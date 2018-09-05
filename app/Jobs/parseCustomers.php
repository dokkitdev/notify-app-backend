<?php

namespace App\Jobs;

use App\Customers;
use App\Service\simProService;
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
        if($this->attempts()>3){ # удаление после 3-х не удачных попыток
            $this->delete();
        }
        preg_match('!\D(\/companies\/|\/individuals\/)(.*+)$!',$this->url,$matches);
        $customer=$this->getCustomer(isset($matches[2])?$matches[2]:false);
        $simProservice=new simProService();
        $parsedCustomer=$simProservice->parseCustomerByUrl($this->url);
        if(!$parsedCustomer)$this->delete();

        $customer->email=isset($parsedCustomer->Email)?$parsedCustomer->Email:'';
        $customer->company_name=isset($parsedCustomer->CompanyName)?$parsedCustomer->CompanyName:'';
        $customer->given_name=isset($parsedCustomer->GivenName)?$parsedCustomer->GivenName:'';
        $customer->family_name=isset($parsedCustomer->FamilyName)?$parsedCustomer->FamilyName:'';
        $customer->simpro_id=$parsedCustomer->ID;
        $customer->address=isset($parsedCustomer->Address->Address)?$parsedCustomer->Address->Address:'';
        $customer->city=isset($parsedCustomer->Address->City)?$parsedCustomer->Address->City:'';
        $customer->state=isset($parsedCustomer->Address->State)?$parsedCustomer->Address->State:'';
        $customer->postal_code=isset($parsedCustomer->Address->PostalCode)?$parsedCustomer->Address->PostalCode:'';
        $customer->country=isset($parsedCustomer->Address->Country)?$parsedCustomer->Address->Country:'';
        $customer->customer_type=isset($parsedCustomer->CustomerType)?$parsedCustomer->CustomerType:'';
        $customer->apiurl=$this->url;
        //$customer->customer_group=isset($parsedCustomer->Profile->CustomerGroup)?$parsedCustomer->Profile->CustomerGroup:'';
        $customer->save();

    }
    private function getCustomer($simpro_id=false){
        if($simpro_id)$customer=Customers::where(['simpro_id'=>$simpro_id])->get()->toArray();
        if($simpro_id&&isset($customer[0]['id']))return Customers::find($customer[0]['id']);
        else return new Customers();
    }
}
