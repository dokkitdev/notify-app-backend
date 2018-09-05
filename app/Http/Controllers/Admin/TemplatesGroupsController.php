<?php

namespace App\Http\Controllers\Admin;

use App\TemplateGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TemplatesGroupsController extends Controller
{
    public function index()
    {
        $title = 'Templates';
        $templates = TemplateGroup::with('templates')->get();
        return view('admin.templates.index',['templatesGroups'=>$templates])->with('title',$title);
    }
}
