<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class TemplateParent extends Model
{
    protected $table = 'template_parent';
    protected $fillable = ['title', 'order'];

    public function templates()
    {
        return $this->hasMany(Templates::class);
    }

}