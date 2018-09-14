<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Service\collectDataService;
use App\SimProContracts;
use App\SimProJobs;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function privateContracts()
    {
        $tg=new collectDataService();
        $where=$tg->getPrivateTemplates();
        if($where) {
            foreach ($where as $k => $val) {
                $contracts[$k] = SimProContracts::where([
                    ['active', '=', 1],
                    ['end_date', '<=', time() + $val['term']],
                    ['end_date', '>=', time() + $val['term'] - $tg->interval]
                ])->get();
            }
        }
        //dd($contracts);
        /*$contracts=SimProContracts::where([
                ['active','=',1],
                ['end_date','<=',time()+$where[1]['term']],
                ['end_date','>=',time()+$where[1]['term']-$tg->interval]
            ])->orWhere([
                ['active','=',1],
                ['end_date','<=',time()+$where[2]['term']],
                ['end_date','>=',time()+$where[2]['term']-$tg->interval]
            ])->orWhere([
                ['active','=',1],
                ['end_date','<=',time()+$where[3]['term']],
                ['end_date','>=',time()+$where[3]['term']-$tg->interval]
            ])->get();
        */


        $title = 'Private Contracts';
        //$contracts=SimProContracts::where(['active',1])->get();
        $res=[];

        if(count($contracts)>0){
            foreach($contracts as $k=>$v){
                foreach($v as $value){
                    $customer=$this->getCustomerByIdTag($value->customers_id,'Private');
                    if(!$customer)continue;
                    $value->letter_state=$k;
                    $tmpArr['contract']=$value;
                    $tmpArr['customer']=$customer;
                    $res[]=$tmpArr;
                }
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
