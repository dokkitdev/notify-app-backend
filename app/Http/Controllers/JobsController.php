<?php

namespace App\Http\Controllers;

use App\SimProJobs;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function updateJob(Request $request){
        $data=$request->all('id');
        $job=SimProJobs::find($data['id']);
        if(isset($job->id)){
            $job->confirm=1;
            $job->save();
            return response()->json(['id'=>$job->id,'confirm' => 1]);
        }else return response()->json(['error' => ['id'=>$job->id,'confirm' => 0]]);
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
}
