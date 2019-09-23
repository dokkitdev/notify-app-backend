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
    const PRIVATE_ANNUAL = 'PRIVATE_ANNUAL';
    const PRIVATE_DEBIT = 'PRIVATE_DEBIT';
    const HOUSING_NO_ACCESS = 'HOUSING_NO_ACCESS';
    const HOUSING_1_ACCESS = 'HOUSING_1_ACCESS';
    const HOUSING_2_ACCESS = 'HOUSING_2_ACCESS';
    const HOUSING_2_ACCESS_LIVEWEST = 'HOUSING_2_ACCESS_LIVEWEST';
    const APPOINTMENT_LETTER = 'APPOINTMENT_LETTER';

    const APPOINTMENT_LETTER_CHL_1 = 'APPOINTMENT_LETTER_CHL_1';
    const APPOINTMENT_LETTER_CHL_2 = 'APPOINTMENT_LETTER_CHL_2';
    const APPOINTMENT_LETTER_CHL_3 = 'APPOINTMENT_LETTER_CHL_3';

    public function parent()
    {
        return $this->belongsTo(TemplateParent::class);
    }

    public function getTitleForFile()
    {
        $title = $this->title ?: '';
        $tag = $this->tag ?: '';
        return strtolower(str_replace(' ', '-', trim($title . ' ' . $tag)));
    }
}