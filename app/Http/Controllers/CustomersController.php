<?php

namespace App\Http\Controllers;

use App\Customers;
use App\SimProContracts;
use App\SimProJobs;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function privateContracts()
    {
        $title = 'Private Contracts';
        $contracts=SimProContracts::where('active',1)->get();
        $res=[];
        if(count($contracts)>0){
            foreach($contracts as $k=>$v){
                $customer=$this->getCustomerByIdTag($v->customers_id,'Private');
                if(!$customer)continue;
                $res[$k]['contract']=$v;
                $res[$k]['customer']=$customer;
            }
        }

        return view('tpl.customers.private',['data'=>$res,'title'=>$title])->with('title',$title);
    }

    public function housingCustomers()
    {
        $title = 'Housing Customers';
        $jobs=SimProJobs::where('set_status_date','<>',null)->get();
        $res=[];
        if(count($jobs)>0){
            foreach($jobs as $k=>$v){
                $res[$k]['job']=$v;
                $res[$k]['customer']=$this->getCustomerBySimproId($v->simpro_customer_id);
            }
        }

        return view('tpl.customers.housing',['data'=>$res,'title'=>$title])->with('title',$title);
    }
    function getCustomerBySimproId($simpro_id){
        $customers=Customers::where('simpro_id',$simpro_id)->get();
        return $customers[0];
    }
    function getCustomerByIdTag($id,$tag){
        $customers=Customers::where('id',$id)->where('customer_group_tag',$tag)->get();
        if(!isset($customers[0]->id))return false;
        return $customers[0];
    }
}
