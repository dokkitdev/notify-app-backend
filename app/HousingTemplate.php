<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HousingTemplate extends Model
{
    protected $table='housing_templates';
    public function housingTemplatesGroups(){
        return $this->belongsTo(HousingTemplateGroup::class);
    }
}
