<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    const TABLE = 'template';
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

    const APPOINTMENT_LETTER_ELECTRIC_CHL_1 = 'APPOINTMENT_LETTER_ELECTRIC_CHL_1';
    const APPOINTMENT_LETTER_ELECTRIC_CHL_2 = 'APPOINTMENT_LETTER_ELECTRIC_CHL_2';
    const APPOINTMENT_LETTER_ELECTRIC_CHL_3 = 'APPOINTMENT_LETTER_ELECTRIC_CHL_3';

    const APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_1 = 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_1';
    const APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_2 = 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_2';
    const APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_3 = 'APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_3';

    const APPOINTMENT_LETTER_GAS_CHL_1 = 'APPOINTMENT_LETTER_GAS_CHL_1';
    const APPOINTMENT_LETTER_GAS_CHL_2 = 'APPOINTMENT_LETTER_GAS_CHL_2';
    const APPOINTMENT_LETTER_GAS_CHL_3 = 'APPOINTMENT_LETTER_GAS_CHL_3';

    public function parent()
    {
        return $this->belongsTo(TemplateParent::class);
    }

    public function getTitleForFile()
    {
        $title = $this->title ?: '';
        $tag = $this->tag ?: '';
        $titleTag = str_replace(['(', ')'], ['',''], $title . ' ' . $tag);
        return strtolower(str_replace(' ', '-', trim($titleTag)));
    }
}
