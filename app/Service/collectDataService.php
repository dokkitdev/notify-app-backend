<?php

namespace App\Service;

use App\Customers;
use App\SimProContracts;
use App\SimProJobs;
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
    public function processContractsTable(){
        $spContracts=SimProContracts::all()->toArray();
        foreach ($spContracts as $v){
            $this->processContract($v['id']);
            /*$contract=SimProContracts::find($v['id']);
            $contract->active=1; # по-умолчанию устанавливаем активность в 1
            $data=json_decode($v['parsedData']);
            # если архивный или истекший устанавливаем активность в 0
            if($data->Archived||$data->Expired)$contract->active=0;
            # если Renewed контракт то устанавливаем активность в 0
            if(strtolower($data->Notes)=='renewed')$contract->active=0;
            # если дата контракта окончилась устанавливаем активность в 0
            if($v['end_date']<time())$contract->active=0;
            $contract->save()*/
        }
    }

    /**
     * @param $id contracts.id
     */
    public function processContract($id){
        $contract=SimProContracts::find($id);
        $contract->active=1; # по-умолчанию устанавливаем активность в 1
        $data=json_decode($contract);
        # если архивный или истекший устанавливаем активность в 0
        if($data->Archived||$data->Expired)$contract->active=0;
        # если Renewed контракт то устанавливаем активность в 0
        if(strtolower($data->Notes)=='renewed')$contract->active=0;
        # если дата контракта окончилась устанавливаем активность в 0
        if($contract->end_date<time())$contract->active=0;
        $contract->save();
    }


    /**
     * Housing
     */

    /**
     * Функция возвращает последний тег из нужных либо false если ни один тег не найден
     * @param $tags - массив объектов тегов который хранится в Jobs
     * @return bool
     */
    public function getLastTag($tags){
        $tagsArr[]='No Access';
        $tagsArr[]='First Access';
        $tagsArr[]='Second Access';
        $tagsArr[]='Final Letter';
        $res=false;
        $tagsArr=array_reverse($tagsArr);
        foreach($tags as $v){
            $tmpTags[]=$v->Name;
        }
        foreach($tagsArr as $val){
            if(in_array($val,$tmpTags)){
                $res=$val;
                break;
            }
        }
        return $res;

    }
    public function processJobsTable(){
        $spJobs=SimProJobs::all()->toArray();
        foreach($spJobs as $v){
            $this->processJob($v['id']);
            /*$job=SimProJobs::find($v['id']);
            $data=json_decode($v['parsedData']);
            # первое условие берем только те Jobs статус которых содержит строку In Progress
            if(!preg_match('~In Progress~',$data->Status->Name)){ # сброс статуса и даты отправки
                $job->status=null;
                $job->set_status_date=null;

            }
            if(count($data->Tags)==0) {
                $job->status=null;
                $job->set_status_date=null;
            }else {
                $tag = $this->getLastTag($data->Tags);
                if (!$tag) { # если нет ни какого тега
                    $job->status = null; # сброс статуса и даты отправки
                    $job->set_status_date = null;
                }

                if ($job->status != $tag) { # случай изменения статуса на стороне simpro или первой загрузки
                    # если сохраненный тег не равен тому который пришел при импорте,
                    # значит нужно пересохранить дату записи тега чтобы начать отсчет 7 дней
                    $job->status = $tag;
                    $job->set_status_date = time();
                } else {
                    $job->status = $job->status;
                    $job->set_status_date = $job->set_status_date;

                }
            }
            $job->save();*/
        }
    }
    public function processJob($id){
        $job=SimProJobs::find($id);
        $data=json_decode($job->parsedData);
        # первое условие берем только те Jobs статус которых содержит строку In Progress
        if(!preg_match('~In Progress~',$data->Status->Name)){ # сброс статуса и даты отправки
            $job->status=null;
            $job->set_status_date=null;

        }
        if(count($data->Tags)==0) {
            $job->status=null;
            $job->set_status_date=null;
        }else {
            $tag = $this->getLastTag($data->Tags);
            if (!$tag) { # если нет ни какого тега
                $job->status = null; # сброс статуса и даты отправки
                $job->set_status_date = null;
            }

            if ($job->status != $tag) { # случай изменения статуса на стороне simpro или первой загрузки
                # если сохраненный тег не равен тому который пришел при импорте,
                # значит нужно пересохранить дату записи тега чтобы начать отсчет 7 дней
                $job->status = $tag;
                $job->set_status_date = time();
            } else {
                $job->status = $job->status;
                $job->set_status_date = $job->set_status_date;

            }
        }
        $job->save();
    }
}