<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $table = 'letters';
    protected $fillable = ['contract_id', 'job_id', 'template_id', 'tosend', 'generated_at', 'sended_at', 'letter', 'email'];
}