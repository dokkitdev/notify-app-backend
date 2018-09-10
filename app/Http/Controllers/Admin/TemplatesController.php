<?php

namespace App\Http\Controllers\Admin;

use App\Service\sesTemplatesService;
use App\Template;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Support\Facades\Storage;

class TemplatesController extends Controller
{
    use FileHelpers;
    public function update($id,Request $request){
        $data=$request->all();
        unset($data['_token']);
        $template=Template::find($id);
        if(!$template)return redirect('admin/templates');
        $template->term=$data['term'];
        if($request->hasFile('upltemplate')&&$request->upltemplate->extension()=='docx') {
            $oldfile=$template->file_link;
            $template->file_link=Storage::disk('s3')->put('templates', $request->file('upltemplate'));
            $exists = Storage::disk('s3')->exists($template->file_link);
            if($exists) {
                $exists = Storage::disk('s3')->exists($oldfile);
                if ($exists) Storage::disk('s3')->delete($oldfile); #delete old template
            }
        }
        $template->save();

        return redirect('admin/templates');

    }
    function getTemplate($id){
        $data='No file';
        $template=Template::find($id);
        $exists = Storage::disk('s3')->exists($template->file_link);
        if($exists){
            $data=Storage::disk('s3')->download($template->file_link);
        }
        return $data;
    }
    function createTemplate(Request $request,$id){
        //$data=$request->all();
        $STS=new sesTemplatesService();

        $data['name']='Tname';
        $data['subject']='Tsubject';
        $data['html_body']='html_body';
        $data['plaintext_body']='plaintext_body';

        $STS->name=$data['name'];
        $STS->subject=$data['subject'];
        $STS->html_body=$data['html_body'];
        $STS->plaintext_body=$data['plaintext_body'];
        $rez=$STS->createSesTemplate();

    }
    function updateTemplate(){

    }
    function deleteTemplate(){

    }
    function emailTemplate(){

    }
}
