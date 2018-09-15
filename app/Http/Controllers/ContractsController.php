<?php

namespace App\Http\Controllers;

use App\PrivateLetter;
use App\SimProContracts;
use App\Template;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function _updateContract(Request $request){
        $data=$request->all();
        $id=$data['id'];
        //$state=$data['state'];
        $contract=SimProContracts::find($id);
        if(isset($contract->id)){
            $contract->confirm=1;
            $contract->save();
            return response()->json(['id'=>$contract->id,'confirm' => 1]);
        }else return response()->json(['error' => ['id'=>$contract->id,'confirm' => 0]]);
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
    public function getTemlateByState($state){
        $templates=Template::where('state',$state)->get();
        if(!$templates[0]->id)return false;
        return $templates[0]->id;
    }
    public function updateContract(Request $request){
        $data=$request->all();
        $id=$data['id'];
        $state=$data['state'];
        $template_id=$this->getTemlateByState($state);
        $contract=SimProContracts::find($id);
        if(isset($contract->id)&&$template_id){
            $contract->confirm=1;
            $contract->save();

            $PL=new PrivateLetter();
            $PL->contract_id=$contract->id;
            $PL->template_id=$template_id;
            $PL->tosend=1;
            $PL->save();

            return response()->json(['id'=>$contract->id,'confirm' => 1]);
        }else return response()->json(['error' => ['id'=>$contract->id,'confirm' => 0]]);
    }
}
