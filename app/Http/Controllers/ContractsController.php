<?php

namespace App\Http\Controllers;

use App\SimProContracts;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function updateContract(Request $request){
        $data=$request->all('id');
        $contract=SimProContracts::find($data['id']);
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
}
