<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    function getParam($name){
        $settings=Settings::where(['name'=>$name])->get()->toArray();
        if(count($settings)==0)return new Settings();
        else return Settings::find($settings[0]['id']);
    }
    function setParam($name,$value){
        $settings=$this->getParam($name);
        $settings->name=$name;
        $settings->value=$value;
        $settings->save();
    }
}
