<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table='templates';
    public function templatesGroups(){
        return $this->belongsTo(TemplateGroup::class);
    }
}
