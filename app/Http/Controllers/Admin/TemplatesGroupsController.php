<?php

namespace App\Http\Controllers\Admin;

use App\Customers;
use App\HousingTemplateGroup;
use App\SimProJobs;
use App\Template;
use App\TemplateGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TemplatesGroupsController extends Controller
{
    public static $terms=[
        7*24*60*60=>'1 week',
        4*7*24*60*60=>'4 weeks',
        8*7*24*60*60=>'8 weeks',
    ];
    public static $tags=[
        1=>'Private'
    ];
    public function index()
    {
        $title = 'Templates';
        $templates = TemplateGroup::with('templates')->get();
        if(count($templates)==0){
            $this->createTemlpateGroup();
            $templates = TemplateGroup::with('templates')->get();
        }
        return view('admin.templates.index',['templatesGroups'=>$templates,'housingTemplatesGroups'=>$this->getHousungTemplates(),'terms'=>self::$terms,'customers'=>$this->getTemlpateGroup()])->with('title',$title);
    }
    public function getHousungTemplates(){
        $templates=[];
        $templates = HousingTemplateGroup::with('housingTemplates')->get();
        return $templates;
    }
    public function getTemlpateGroup(){
        # спсиок Jobs + Customer для вывода выпадающего списка без существующих
        # все
        $templatesGroups=HousingTemplateGroup::all();
        $htg=[];
        foreach($templatesGroups as $v){
            $htg[]=$v->customer_id;
        }
        $jobs=SimProJobs::where('set_status_date','<>',null)->get();
        $simpro_customers_Ids=[];
        foreach($jobs as $val){
            $simpro_customers_Ids[]=$val->simpro_customer_id;
        }
        $customers=Customers::whereIn('simpro_id', $simpro_customers_Ids)->whereNotIn('id', $htg)->get();
        $res=[];
        foreach ($customers as $v){
            $res[$v->id]=$v->company_name;
        }
        return $res;
    }

    /**
     * create templates by $tags array
     * @return bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createTemlpateGroup(){
        foreach(static::$tags as $k=>$v) {
            $tg = new TemplateGroup();
            $tg->customer_group_tag_id = $k;
            $tg->customer_group_tag = static::$tags[$k];
            $tg->save();
            if (!$tg->id) return redirect('admin/templates');
            for ($i = 1; $i <= 3; $i++) {
                $template = new Template();
                $template->template_group_id = $tg->id;
                $template->state = $i;
                $template->save();
            }
        }
        return true;

    }
}
