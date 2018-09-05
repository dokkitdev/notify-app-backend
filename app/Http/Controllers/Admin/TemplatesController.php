<?php

namespace App\Http\Controllers\Admin;

use App\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class TemplatesController extends Controller
{
    public function update($id,Request $request){
        $data=$request->all();
        unset($data['_token']);
        $template=Template::find($id);
        if(!$template)return redirect('admin/templates');
        $template->term=$data['term'];
        if($request->hasFile('upltemplate')) {

        }
        $template->save();

        return redirect('admin/templates');

    }
}
