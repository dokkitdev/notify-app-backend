<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HousingTemplateGroup extends Model
{
    protected $table='housing_templates_groups';
    public function housingTemplates()
    {
        return $this->hasMany('App\HousingTemplate');
    }
}
