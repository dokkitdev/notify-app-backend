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
        $title = 'Private Contracts';

        $tg=new collectDataService();
        $where=$tg->getPrivateTemplates();
        $time=strtotime(date("Y-m-d",time()).' 00:00:00'); #today at 00:00:00 (day start)
        $contracts=[];
        $contracts['contracts']=[];
        if($where) {
            foreach ($where as $k => $val) {

                $dateS=($time + $val['term'])-24*60*60-$tg->interval; #get more or = than 1/4/8 weeks in future - $tg->interval
                $dateE=$time + $val['term']-24*60*60; #not less or = than 1/4/8 weeks in future

                $contracts['contracts'][$k] = SimProContracts::where([
                    ['active', '=', 1],
                    //['delete', '=', 0],
                    ['end_date', '>=', $dateS],
                    ['end_date', '<=', $dateE]
                ])->get();
                $contracts['templates'][$k]=$val;
                //print date("Y-m-d",$dateS).'<br>';
                //print date("Y-m-d",$dateE).'<br><br>';
            }

        }
        $res=[];
        if(count($contracts['contracts'])>0){
            foreach($contracts['contracts'] as $k=>$v){
                foreach($v as $value){
                    $customer=$this->getCustomerByIdTag($value->customers_id,'Private');
                    if(!$customer)continue;
                    $value->state=$contracts['templates'][$k]['state'];
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
        $time=strtotime(date("Y-m-d",time()).' 00:00:00'); #today at 00:00:00 (day start)

        $tg=new collectDataService();
        $where=$tg->getHoisingTemplates();

        $jobs=SimProJobs::where([
                ['status','!=',''],
                ['set_status_date','>=',$time-24*60*60],
                ['set_status_date','<=',$time+24*60*60]
            ])->get();

        $res=[];
        if(count($jobs)>0){
            foreach($jobs as $k=>$v){
                $res[$k]['job']=$v;
                $tmpCustomer=$this->getCustomerBySimproId($v->simpro_customer_id);
                if(in_array($tmpCustomer->id,$where['customers'])){
                    $tmpCustomer->tpl=true;
                    //dd($where['where'][$tmpCustomer->id]['templates']);
                    foreach ($where['where'][$tmpCustomer->id]['templates'] as $key=>$val){
                        if($res[$k]['job']->status==$val)$res[$k]['job']->letter_template=$key;
                    }
                } # проверка на существование группы шаблонов для customer
                else $tmpCustomer->tpl=false;



                $res[$k]['customer']=$tmpCustomer;
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
