<?php

namespace App\Http\Controllers;

use App\Customers;
use App\HousingTemplateGroup;
use App\Jobs\CreateLetters;
use App\Letter;
use App\Service\collectDataService;
use App\SimProJobs;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function processJobs(Request $request){
        $jobs = $request->all()['jobs'];
       // dd($jobs);
        foreach ($jobs as $k => $v) {
            $job = SimProJobs::find($k);
            $job->confirm = 1; #todo надо решить что делать при обновлении
            $job->save();
            $cds=new collectDataService();
            $nextTag=$cds->getNextTag($v);
            if(!$nextTag)continue;
            $template_id=$this->getTemplateByStateJob($nextTag,$job);

            //$template_id = $this->getHoisingTemlateByStateCustomer($v);
            //if (!$template_id) continue; #todo должно собирать ошибки чтобы затем вернуть

            if(Letter::where('contract_id', $job->id)->where('template_id', $template_id)->count() === 0){
                $letter = new Letter();
                $letter->job_id = $job->id;
                $letter->housing_template_id = $template_id;
                $letter->tosend = 1;
                $letter->save();
                CreateLetters::dispatch($letter->id)->delay(now()->addSecond(5));
            }
            return redirect('/housingCustomers')->with('ok','Sended');


        }
    }
    public function getTemplateByStateJob($tagName,$job){
        $customer=Customers::where('simpro_id','=',$job->simpro_customer_id)->get();
        if(!isset($customer[0]->id))return false;
        $templates = HousingTemplateGroup::with('housingTemplates')
            ->where('customer_id','=',$customer[0]->id)->get();
        if(!isset($templates[0]['housingTemplates']))return false;

        foreach($templates[0]['housingTemplates'] as $v){
            if($v->state==$tagName)return $v->id;
        }
        return false;
    }
    public function deleteJob(Request $request){
        $data=$request->all('id');
        $job=SimProJobs::find($data['id']);
        if(isset($job->id)) {
            $job->delete = 1;
            $job->save();
            return response()->json(['id'=>$job->id,'delete' => 1]);
        }else return response()->json(['error' => ['id'=>$job->id,'delete' => 0]]);
    }
    public function undeleteJob(Request $request){
        $data=$request->all('id');
        $job=SimProJobs::find($data['id']);
        if(isset($job->id)) {
            $job->delete = 0;
            $job->save();
            return response()->json(['id'=>$job->id,'delete' => 0]);
        }else return response()->json(['error' => ['id'=>$job->id,'delete' => 1]]);
    }
}
