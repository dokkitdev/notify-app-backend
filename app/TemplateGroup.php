<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateGroup extends Model
{
    protected $table='templates_groups';
    public function templates()
    {
        return $this->hasMany('App\Template');
    }
}
