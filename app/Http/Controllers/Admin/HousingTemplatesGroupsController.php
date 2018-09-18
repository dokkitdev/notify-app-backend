<?php

namespace App\Http\Controllers\Admin;

use App\Customers;
use App\HousingTemplate;
use App\HousingTemplateGroup;
use App\Http\Controllers\Controller;
use App\Service\collectDataService;
use App\SimProJobs;
use App\Template;
use Illuminate\Http\Request;

class HousingTemplatesGroupsController extends Controller
{
    public function createTemplateGroup(Request $request){
        $data=$request->all();
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
            $template->save();
        }
        return redirect('admin/templates');
    }
}
