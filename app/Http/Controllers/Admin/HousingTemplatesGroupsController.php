<?php

namespace App\Http\Controllers\Admin;

use App\Customers;
use App\HousingTemplate;
use App\HousingTemplateGroup;
use App\Http\Controllers\Controller;
use App\Service\collectDataService;
use App\Service\sesTemplatesService;
use App\SimProJobs;
use App\Template;
use Illuminate\Http\Request;

class HousingTemplatesGroupsController extends Controller
{
    public function createTemplateGroup(Request $request){
        $data=$request->all();
        if(!isset($data['customer']))
            return redirect(route('templates.index'));
        $customer=Customers::find($data['customer']);
        if(!$customer->id)return redirect('admin/templates');
        $tg = new HousingTemplateGroup();
        $tg->customer_id = $data['customer'];
        $tg->company_name = $customer->company_name;
        $tg->save();
        if (!$tg->id) return redirect('admin/templates');
        $cds=new collectDataService();
        $tagsArr=$cds->tagsArr;
        for ($i = 0; $i <= 2; $i++) {
            $template = new HousingTemplate();
            $template->housing_template_group_id = $tg->id;
            $template->state = $tagsArr[$i];
            $template->name = 'HousingLetter' . ($i + 1);
            $path = resource_path('templates/pdf/' . $template->name . '.html');
            if(file_exists($path))
                $template->html_pdf = file_get_contents($path);
            else
                $template->html_pdf = '';
            $template->save();
//            $STS = new sesTemplatesService();
//            $rez = $STS->createSesTemplate('HousingTemplate' . $template->id, $data['html_body'], $data['subject']/*, $data['plaintext_body']*/);
        }
        return redirect('admin/templates');
    }
}
