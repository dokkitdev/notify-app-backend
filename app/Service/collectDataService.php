<?php

namespace App\Service;

use App\Customers;
use App\TemplateGroup;

class collectDataService
{
    private $interval=60*60*24*7; # one week

    public function collectData(){
        $templatesGroups=TemplateGroup::where('customer_group_tag_id','<>',0)->with('templates')->get();
        if(count($templatesGroups)==0)return false;
        $where=[];
        foreach($templatesGroups as $tg){
            $Tmpwhere[$tg->id]['customer_group_tag_id']=$tg->customer_group_tag_id;
            if(count($tg->templates)==0)continue;
            foreach($tg->templates as $template){
                $where[$tg->id][$template->id]['state']=$template->state;
                $where[$tg->id][$template->id]['term']=$template->term;
                $where[$tg->id][$template->id]['customer_group_tag_id']=$tg->customer_group_tag_id;
            }

        }
        if(count($where)==0)return false;
        foreach($where as $v){
            if(count($v)==0)continue;
            foreach ($v as $val){
                //$val['term']=60*60*24*365;
                $customers[$val['state']]=Customers::where(['customer_group_tag_id'=>$val['customer_group_tag_id']])
                    ->where('end_date','<=',time()+$val['term'])
                    ->where('end_date','>=',time()+$val['term']-$this->interval)
                    ->get();

         //       print $val['term'].'<br>';
          //      print date("Y-m-d",time()+$val['term']-$this->interval).'<br>';
         //       print date("Y-m-d",time()+$val['term']).'<br>';
            }
        }
        foreach ($customers as $k=>$customer){
            foreach($customer as $v){
                // may insert the worker
                // customer_id and $k - letter state
                // letters generation may insert there
                $c=Customers::find($v->id);
                $c->letter_state=$k;
                $c->save();
            }
        }

    }
}