<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    protected $table = 'template';
    protected $fillable = ['term', 'title', 'tag', 'alias', 'html_body', 'html', 'subject', 'docx', 'pdf', 'is_html'];

    const PRIVATE_1_WEEK = 'PRIVATE_1_WEEK';
    const PRIVATE_4_WEEK = 'PRIVATE_4_WEEK';
    const PRIVATE_8_WEEK = 'PRIVATE_8_WEEK';
    const HOUSING_NO_ACCESS = 'HOUSING_NO_ACCESS';
    const HOUSING_1_ACCESS = 'HOUSING_1_ACCESS';
    const HOUSING_2_ACCESS = 'HOUSING_2_ACCESS';
    const APPOINTMENT_LETTER = 'APPOINTMENT_LETTER';


    public function parent()
    {
        return $this->belongsTo(TemplateParent::class);
    }
}