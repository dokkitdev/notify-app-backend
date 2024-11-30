<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TemplateParent extends Model
{
    protected $table = 'template_parent';
    protected $fillable = ['title', 'order'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templates()
    {
        return $this->hasMany(Templates::class);
    }

}
