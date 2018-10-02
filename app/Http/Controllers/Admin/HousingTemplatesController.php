<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PDFGenerator;
use App\HousingTemplate;
use App\Http\Controllers\Controller;
use App\Service\sesTemplatesService;
use Illuminate\Http\Request;

class HousingTemplatesController extends Controller
{
    public function create($id)
    {
        $template=HousingTemplate::find($id);
        if(!$template)return redirect('/admin/templates');
        $title = 'Create template';
        return view('admin.templates.housing.create',['template'=>$template])->with('title',$title);
    }
    function createTemplate(Request $request){
        $data=$request->all();
        $id=$data['id'];
        $STS=new sesTemplatesService();
        $rez=$STS->createSesTemplate('HousingTemplate'.$id,$data['html_body'],$data['subject']/*,$data['plaintext_body']*/);
        if($rez=='200'){
            $template=HousingTemplate::find($id);
            $template->name='HousingTemplate'.$id;
            //$template->term=$data['term'];
            $template->html_pdf=$data['html_pdf'];
            $template->save();
        }else{
            $ses=$this->deleteTemplate('HousingTemplate'.$id);
            if($ses)$this->createTemplate($request);
        }
        return redirect('/admin/templates');

    }
    function updateTemplate(Request $request,$id){
        $data=$request->all();
        $STS=new sesTemplatesService();
        $template=HousingTemplate::find($id);
        if(!$template)return false;
        $rez=$STS->updateSesTemplate('HousingTemplate'.$id,$data['html_body'],$data['subject']/*,$data['plaintext_body']*/);
        if($rez=='200'){
            $template->name='HousingTemplate'.$id;
            //$template->term=$data['term'];
            $template->html_pdf=$data['html_pdf'];
            $template->save();
        }
        return redirect('/admin/templates');

    }
    function getTemplate($id){
        $title = 'Update template';
        $STS=new sesTemplatesService();
        $template=HousingTemplate::find($id);
        if(!$template||$template->name=='')return false;
        $ses=$STS->getSesTemplate($template->name);
        if(!is_array($ses))return redirect('/admin/templates');
        return view('admin.templates.housing.edit',['template'=>$template,'ses'=>$ses])->with('title',$title);
    }
    function deleteTemplate($name){
        $STS=new sesTemplatesService();
        $ses=$STS->deleteSesTemplate($name);
        if($ses=='200')return true;
        else return false;
    }
    function emailTemplate($id){
        $STS=new sesTemplatesService();
        $template=HousingTemplate::find($id);
        if(!$template||$template->name=='')return false;
        $STS->sendSesTemplateEmail($template->name,Auth::user()->email,Auth::user()->email,['name'=>Auth::user()->name]);
        return redirect('/admin/templates');
    }

    public function downloadPDF(PDFGenerator $pdfGenerator, $id)
    {
        $template = HousingTemplate::find($id);
        if(!$template || $template->html_pdf == '')
            return false;
        $result = $pdfGenerator->generatePDF($template->html_pdf, [], md5(rand(0, 99999) . time()) . '.pdf', false);
        if($result === false)
            return response()->view('errors.main', [], 500);
        else
            return response()->download($result, (trim($template->name) == '' ? 'template' : $template->name) . '.pdf')->deleteFileAfterSend(true);
    }
}
