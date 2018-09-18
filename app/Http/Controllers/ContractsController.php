<?php

namespace App\Http\Controllers;

use App\PrivateLetter;
use App\SimProContracts;
use App\Template;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function processContracts(Request $request){
        $contracts=$request->all()['contract'];
        //dd($contracts);
        foreach($contracts as $k=>$v){
            $contract=SimProContracts::find($k);
            $contract->confirm=1; #todo надо решить что делать при обновлении
            $contract->save();

            $template_id=$this->getTemlateByState($v);
            if(!$template_id)continue; #todo должно собирать ошибки чтобы затем вернуть

            $PL=new PrivateLetter(); #todo найти запись и если она есть то не создавать новую!!
            $PL->contract_id=$contract->id;
            $PL->template_id=$template_id;
            $PL->tosend=1;
            $PL->save();
        }
        return redirect('/privateContracts');
    }


    public function deleteContract(Request $request){
        $data=$request->all('id');
        $contract=SimProContracts::find($data['id']);
        if(isset($contract->id)) {
            $contract->delete = 1;
            $contract->save();
            return response()->json(['id'=>$contract->id,'delete' => 1]);
        }else return response()->json(['error' => ['id'=>$contract->id,'delete' => 0]]);
    }
    public function undeleteContract(Request $request){
        $data=$request->all('id');
        $contract=SimProContracts::find($data['id']);
        if(isset($contract->id)) {
            $contract->delete = 0;
            $contract->save();
            return response()->json(['id'=>$contract->id,'delete' => 0]);
        }else return response()->json(['error' => ['id'=>$contract->id,'delete' => 1]]);
    }
    public function getTemlateByState($state){
        $templates=Template::where('state',$state)->get();
        if(!$templates[0]->id)return false;
        return $templates[0]->id;
    }
}
