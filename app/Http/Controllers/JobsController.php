<?php

namespace App\Http\Controllers;

use App\SimProJobs;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function processJobs(Request $request){
        $jobs = $request->all()['jobs'];
        dd($jobs);
        foreach ($jobs as $k => $v) {
            $job = SimProJobs::find($k);
            $job->confirm = 1; #todo надо решить что делать при обновлении
            $job->save();

            $template_id = $this->getHoisingTemlateByStateCustomer($v);
            if (!$template_id) continue; #todo должно собирать ошибки чтобы затем вернуть

            $PL = new Letter(); #todo найти запись и если она есть то не создавать новую!!
            $PL->job_id = $job->id;
            $PL->template_id = $template_id;
            $PL->tosend = 1;
            $PL->save();
        }
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
