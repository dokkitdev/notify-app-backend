<?php

namespace App\Http\Controllers;

use App\Customers;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index($id)
    {
        $title = 'Customers';
        $tags=Customers::where('customer_group_tag_id','<>',0)->selectRaw('count(customer_group_tag_id) AS customer_group_tag_num,ANY_VALUE(customer_group_tag) AS tag,customer_group_tag_id')->groupBy('customer_group_tag_id')->get();
        if(count($tags)>0){
            foreach($tags as $v){
                $t[]=['id'=>$v->customer_group_tag_id,'name'=>($v->tag!=''?$v->tag:'NoName')];
            }
        }
        $customers = Customers::where(['customer_group_tag_id'=>$id])->get();
        if(count($customers)>0){
            $title=' "'.$customers[0]->customer_group_tag.'" group';
        }
        return view('tpl.customers.index',['customers'=>$customers,'title'=>$title,'tags'=>$t])->with('title',$title);
    }
}
