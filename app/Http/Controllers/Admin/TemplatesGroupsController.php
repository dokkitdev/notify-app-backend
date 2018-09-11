<?php

namespace App\Http\Controllers\Admin;

use App\TemplateGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TemplatesGroupsController extends Controller
{
    public $terms=[
        7*24*60*60=>'1 week',
        4*7*24*60*60=>'4 weeks',
        8*7*24*60*60=>'8 weeks',
    ];
    public function index()
    {
        $title = 'Templates';
        $templates = TemplateGroup::with('templates')->get();
        return view('admin.templates.index',['templatesGroups'=>$templates,'terms'=>$this->terms])->with('title',$title);
    }
}
