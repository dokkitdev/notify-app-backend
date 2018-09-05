<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public function templatesGroups(){
        return $this->belongsTo(TemplateGroup::class);
    }
}
