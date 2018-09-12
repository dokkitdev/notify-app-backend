<?php

namespace App\Http\Controllers\Admin;

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
        1=>'Private',
        2=>'Housing',
    ];
    public function index()
    {
        $title = 'Templates';
        $templates = TemplateGroup::with('templates')->get();
        if(count($templates)==0){
            $this->createTemlpateGroup();
            $templates = TemplateGroup::with('templates')->get();
        }
        return view('admin.templates.index',['templatesGroups'=>$templates,'terms'=>self::$terms])->with('title',$title);
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
