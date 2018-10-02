<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PDFGenerator;
use App\Service\sesTemplatesService;
use App\Template;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplatesController extends Controller
{
    use FileHelpers;
    public function update_old($id,Request $request){
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
    function getTemplate_old($id){
        $data='No file';
        $template=Template::find($id);
        $exists = Storage::disk('s3')->exists($template->file_link);
        if($exists){
            $data=Storage::disk('s3')->download($template->file_link);
        }
        return $data;
    }
    public function create($id)
    {
        $template=Template::find($id);
        if(!$template)return redirect('/admin/templates');
        $title = 'Create template';
        return view('admin.templates.create',['template'=>$template,'terms'=>TemplatesGroupsController::$terms])->with('title',$title);
    }
    function createTemplate(Request $request){
        $data=$request->all();
        $id=$data['id'];
        $STS=new sesTemplatesService();
        $rez=$STS->createSesTemplate('Template'.$id,$data['html_body'],$data['subject']/*,$data['plaintext_body']*/);
        if($rez=='200'){
            $template=Template::find($id);
            $template->name='Template'.$id;
            $template->term=$data['term'];
            $template->html_pdf=$data['html_pdf'];
            $template->save();
        }else{
            $ses=$this->deleteTemplate('Template'.$id);
            if($ses)$this->createTemplate($request);
        }
        return redirect('/admin/templates');

    }
    function updateTemplate(Request $request,$id){
        $data=$request->all();
        $STS=new sesTemplatesService();
        $template=Template::find($id);
        if(!$template)return false;
        $rez=$STS->updateSesTemplate('Template'.$id,$data['html_body'],$data['subject']/*,$data['plaintext_body']*/);
        if($rez=='200'){
            $template->name='Template'.$id;
            $template->term=$data['term'];
            $template->html_pdf=$data['html_pdf'];
            $template->save();
        }
        return redirect('/admin/templates');

    }
    function getTemplate($id){
        $title = 'Update template';
        $STS=new sesTemplatesService();
        $template=Template::find($id);
        if(!$template||$template->name=='')return false;
        $ses=$STS->getSesTemplate($template->name);
        if(!is_array($ses))return redirect('/admin/templates');
        return view('admin.templates.edit',['template'=>$template,'ses'=>$ses,'terms'=>TemplatesGroupsController::$terms])->with('title',$title);
    }
    function deleteTemplate($name){
        $STS=new sesTemplatesService();
        $ses=$STS->deleteSesTemplate($name);
        if($ses=='200')return true;
        else return false;
    }
    function emailTemplate($id){
        $STS=new sesTemplatesService();
        $template=Template::find($id);
        if(!$template||$template->name=='')return false;
        $STS->sendSesTemplateEmail($template->name,Auth::user()->email,Auth::user()->email,['name'=>Auth::user()->name]);
        return redirect('/admin/templates');
    }

    public function downloadPDF(PDFGenerator $pdfGenerator, $id)
    {
        $template = Template::find($id);
        if(!$template || $template->html_pdf == '')
            return false;
        $result = $pdfGenerator->generatePDF($template->html_pdf, [], md5(rand(0, 99999) . time()) . '.pdf', false);
        if($result === false)
            return response()->view('errors.main', [], 500);
        else
            return response()->download($result, $template->name . '.pdf')->deleteFileAfterSend(false);
    }
}
